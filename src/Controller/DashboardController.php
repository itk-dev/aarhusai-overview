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

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(Request $request, EntityManagerInterface $em, OpenWebUiClientFactory $clientFactory): Response
    {
        $siteKeys = $clientFactory->getSiteKeys();
        $activeSite = $request->query->get('site');
        if (null !== $activeSite && !\in_array($activeSite, $siteKeys, true)) {
            $activeSite = null;
        }

        $siteHealth = [];
        foreach ($siteKeys as $key) {
            try {
                $siteHealth[$key] = $clientFactory->createClient($key)->isHealthy();
            } catch (\InvalidArgumentException) {
                $siteHealth[$key] = null;
            }
        }

        $criteria = null !== $activeSite ? ['site' => $activeSite] : [];

        return $this->render('dashboard/index.html.twig', [
            'models' => $em->getRepository(Model::class)->findBy($criteria),
            'users' => $em->getRepository(User::class)->findBy($criteria),
            'groups' => $em->getRepository(Group::class)->findBy($criteria),
            'siteKeys' => $siteKeys,
            'siteHealth' => $siteHealth,
            'activeSite' => $activeSite,
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
