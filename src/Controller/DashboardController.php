<?php

namespace App\Controller;

use App\Entity\Model;
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

        return $this->render('dashboard/index.html.twig', [
            'models' => $models,
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
            $this->addFlash('success', sprintf('[%s] Synced %d models.', $site, $counts['models']));
        }

        return $this->redirectToRoute('dashboard');
    }
}
