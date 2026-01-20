<?php

declare(strict_types=1);

namespace App\Controller\Admin\Excuse;

use App\Entity\Excuse;
use App\Form\ExcuseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/excuses/{id}/edit', name: 'admin_excuse_edit', methods: ['GET', 'POST'])]
class UpdateExcuseController extends AbstractController
{
    public function __invoke(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExcuseType::class, $excuse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_excuse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/excuse/edit.html.twig', [
            'excuse' => $excuse,
            'form' => $form->createView(),
        ]);
    }
}
