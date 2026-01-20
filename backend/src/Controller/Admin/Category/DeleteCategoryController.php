<?php

declare(strict_types=1);

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories/{id}', name: 'admin_category_delete', methods: ['POST'])]
class DeleteCategoryController extends AbstractController
{
    public function __invoke(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token', ''))) {
            // Soft delete
            $category->setDeletedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
