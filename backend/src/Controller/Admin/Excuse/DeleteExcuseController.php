<?php

declare(strict_types=1);

namespace App\Controller\Admin\Excuse;

use App\Entity\Excuse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/excuses/{id}', name: 'admin_excuse_delete', methods: ['POST'])]
class DeleteExcuseController extends AbstractController
{
    public function __invoke(Request $request, Excuse $excuse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $excuse->getId(), $request->request->get('_token', ''))) {
            // Soft delete
            $excuse->setDeletedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_excuse_index', [], Response::HTTP_SEE_OTHER);
    }
}
