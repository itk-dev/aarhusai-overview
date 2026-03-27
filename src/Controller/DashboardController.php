<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Model;
use App\Entity\User;
use App\Service\OpenWebUiClientFactory;
use App\Service\OpenWebUiSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(Request $request, EntityManagerInterface $em, OpenWebUiClientFactory $clientFactory, CacheInterface $cache): Response
    {
        $siteKeys = $clientFactory->getSiteKeys();
        $activeSite = $request->query->get('site');
        if (null !== $activeSite && !\in_array($activeSite, $siteKeys, true)) {
            $activeSite = null;
        }

        $siteHealth = [];
        foreach ($siteKeys as $key) {
            try {
                $siteHealth[$key] = $cache->get('openwebui_health_'.md5($key), function (ItemInterface $item) use ($clientFactory, $key) {
                    $item->expiresAfter(30);

                    return $clientFactory->createClient($key)->isHealthy();
                });
            } catch (\InvalidArgumentException) {
                $siteHealth[$key] = null;
            }
        }

        $criteria = null !== $activeSite ? ['site' => $activeSite] : [];
        $models = $em->getRepository(Model::class)->findBy($criteria);
        $users = $em->getRepository(User::class)->findBy($criteria);
        $groups = $em->getRepository(Group::class)->findBy($criteria);

        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->getExternalId()] = $user->getName();
        }
        $groupMap = [];
        foreach ($groups as $group) {
            $groupMap[$group->getExternalId()] = $group->getName();
        }

        $modelAccessGrants = [];
        foreach ($models as $model) {
            $grouped = [];
            foreach ($model->getAccessGrants() as $grant) {
                $key = $grant->getPrincipalType().':'.$grant->getPrincipalId();
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'principalType' => $grant->getPrincipalType(),
                        'principalId' => $grant->getPrincipalId(),
                        'principalName' => match ($grant->getPrincipalType()) {
                            'group' => $groupMap[$grant->getPrincipalId()] ?? $grant->getPrincipalId(),
                            'user' => $userMap[$grant->getPrincipalId()] ?? $grant->getPrincipalId(),
                            default => $grant->getPrincipalId(),
                        },
                        'permissions' => [],
                    ];
                }
                $grouped[$key]['permissions'][] = $grant->getPermission();
            }

            // Sort: groups first, then users
            usort($grouped, fn ($a, $b) => ('group' === $a['principalType'] ? 0 : 1) <=> ('group' === $b['principalType'] ? 0 : 1));

            // Sort permissions within each entry
            foreach ($grouped as &$entry) {
                sort($entry['permissions']);
            }

            $modelAccessGrants[$model->getExternalId()] = $grouped;
        }

        return $this->render('dashboard/index.html.twig', [
            'models' => $models,
            'users' => $users,
            'groups' => $groups,
            'siteKeys' => $siteKeys,
            'siteHealth' => $siteHealth,
            'activeSite' => $activeSite,
            'modelAccessGrants' => $modelAccessGrants,
        ]);
    }

    #[Route('/sync', name: 'dashboard_sync', methods: ['POST'])]
    public function sync(Request $request, OpenWebUiSyncService $syncService): Response
    {
        if (!$this->isCsrfTokenValid('sync', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard');
        }

        $results = $syncService->syncAll();

        foreach ($results as $site => $counts) {
            if (isset($counts['error'])) {
                $this->addFlash('warning', sprintf('[%s] Skipped: %s', $site, $counts['error']));
                continue;
            }
            $this->addFlash('success', sprintf('[%s] Synced %d models, %d users, %d groups.', $site, $counts['models'], $counts['users'], $counts['groups']));
        }

        return $this->redirectToRoute('dashboard');
    }
}
