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

#[Route('/admin/excuses/new', name: 'admin_excuse_new', methods: ['GET', 'POST'])]
class CreateExcuseController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $excuse = new Excuse();
        $form = $this->createForm(ExcuseType::class, $excuse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($excuse);
            $entityManager->flush();

            return $this->redirectToRoute('admin_excuse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/excuse/new.html.twig', [
            'excuse' => $excuse,
            'form' => $form->createView(),
        ]);
    }
}
