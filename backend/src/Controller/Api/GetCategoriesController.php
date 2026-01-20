<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories_list', methods: ['GET'])]
class GetCategoriesController extends AbstractController
{
    public function __invoke(CategoryRepository $categoryRepository): JsonResponse
    {
        // Finds active categories (not deleted) that have at least 5 excuses
        $categories = $categoryRepository->findActiveWithMinExcuses(5);

        $data = array_map(fn($c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
        ], $categories);

        if (count($data) < 5) {
            return new JsonResponse(['error' => 'Not enough categories'], 400);
        }

        return new JsonResponse($data);
    }
}
