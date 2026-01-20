<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ExcuseController extends AbstractController
{
    private const EXCUSES = [
        1 => 'Lo siento, mi perro se comió mi router.',
        2 => 'Estaba compilando esto en mi mente y colapsé.',
        3 => 'Un rayo cósmico invirtió un bit en mi memoria.',
        4 => 'El gato caminó sobre el teclado y borró todo.',
        5 => 'Pensé que hoy era domingo.',
        6 => 'Mi conexión a internet decidió tomarse el día libre.',
        7 => 'Estaba ocupado actualizando Vim.',
        8 => 'Se me olvidó cómo salir de Vim.',
        9 => 'La cafetera explotó y tuve que limpiar.',
        10 => 'Estaba esperando a que Docker terminara de compilar.'
    ];

    #[Route('/excuse/{id}', name: 'get_excuse', methods: ['GET'])]
    public function __invoke(int $id): JsonResponse
    {
        // Simple logic for now: return a static string based on ID, 
        // or a default one if ID is out of range.
        // In a real app, this would fetch from DB based on "type" (id).

        $text = self::EXCUSES[$id] ?? 'No tengo excusa para eso...';

        return new JsonResponse([
            'text' => $text,
            'type' => $id
        ]);
    }
}
