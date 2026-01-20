<?php

declare(strict_types=1);

namespace App\Controller\Admin\Category;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories', name: 'admin_category_index', methods: ['GET'])]
class ListCategoryController extends AbstractController
{
    public function __invoke(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }
}
