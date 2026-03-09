<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Model;
use App\Entity\User;
use App\Service\OpenWebUiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
