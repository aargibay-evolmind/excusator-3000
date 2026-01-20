<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\ExcuseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/excuse', name: 'api_get_excuse', methods: ['GET'])]
class GetExcuseController extends AbstractController
{
    public function __invoke(Request $request, ExcuseRepository $excuseRepository): JsonResponse
    {
        $categoryId = $request->query->getInt('category_id');

        if (!$categoryId) {
            return new JsonResponse(['error' => 'Category ID is required'], 400);
        }

        $excuse = $excuseRepository->findRandomByCategory($categoryId);

        if (!$excuse) {
            return new JsonResponse(['error' => 'No excuse found for this category'], 404);
        }

        return new JsonResponse([
            'id' => $excuse->getId(),
            'content' => $excuse->getContent(),
            'category_id' => $excuse->getCategory()?->getId()
        ]);
    }
}
