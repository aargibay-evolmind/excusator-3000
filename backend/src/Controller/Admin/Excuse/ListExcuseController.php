<?php

declare(strict_types=1);

namespace App\Controller\Admin\Excuse;

use App\Repository\ExcuseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/excuses', name: 'admin_excuse_index', methods: ['GET'])]
class ListExcuseController extends AbstractController
{
    public function __invoke(ExcuseRepository $excuseRepository): Response
    {
        return $this->render('admin/excuse/index.html.twig', [
            'excuses' => $excuseRepository->findBy(['deletedAt' => null]),
        ]);
    }
}
