<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Model;
use App\Entity\User;
use App\Service\OpenWebUiClient;
use App\Service\OpenWebUiSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(EntityManagerInterface $em, OpenWebUiClient $client): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'models' => $em->getRepository(Model::class)->findAll(),
            'users' => $em->getRepository(User::class)->findAll(),
            'groups' => $em->getRepository(Group::class)->findAll(),
            'healthy' => $client->isHealthy(),
        ]);
    }

    #[Route('/sync', name: 'dashboard_sync', methods: ['POST'])]
    public function sync(Request $request, OpenWebUiSyncService $syncService): Response
    {
        if (!$this->isCsrfTokenValid('sync', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('dashboard');
        }

        $syncService->syncAll();
        $this->addFlash('success', 'Data synced successfully.');

        return $this->redirectToRoute('dashboard');
    }
}
