<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Excuse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const CATEGORIES_WITH_EXCUSES = [
        'Desarrolladores' => [
            'En mi máquina funciona.',
            'Es un problema de caché, limpia tu navegador.',
            'Eso no es un bug, es una feature.',
            'El código se está compilando todavía.',
            'No toqué esa parte del código.',
            'Estamos esperando a que se actualicen las librerías.',
            'Voy a tener que refactorizar todo para arreglar eso.'
        ],
        'Diseñadores' => [
            'El espacio negativo es parte del diseño.',
            'Ese color se ve diferente en mi mac retina.',
            'Estoy esperando inspiración.',
            'El archivo está corrupto.',
            'No me pasaron los assets en alta resolución.',
            'La fuente no ha cargado correctamente.'
        ],
        'Project Managers' => [
            'Lo revisaremos en la próxima daily.',
            'Eso está fuera del alcance del sprint.',
            'Prioridades cambiantes del cliente.',
            'Estamos bloqueados por dependencias externas.',
            'Vamos a tener que hacer una reunión para discutir eso.',
            'El Gantt dice otra cosa.'
        ],
        'SysAdmins' => [
            'El firewall lo está bloqueando.',
            'Reinicia el servidor a ver qué pasa.',
            'Hubo un pico de tráfico inesperado.',
            'Es culpa del proveedor de la nube.',
            'El script de backup se comió los recursos.',
            'Hay que purgar los logs, disco lleno.'
        ],
        'Testers' => [
            'No pude reproducir el error.',
            'El entorno de pruebas estaba caído.',
            'Eso funcionaba ayer.',
            'Faltan datos de prueba.',
            'El navegador se actualizó solo y rompió los tests.',
            'Es un caso borde (edge case) muy raro.'
        ],
        'Recursos Humanos' => [
            'Estamos procesando tu solicitud.',
            'El sistema de nóminas está en mantenimiento.',
            'Es política de la empresa.',
            'Estamos esperando la firma del director.',
            'El correo se fue a spam.',
            'Estamos en medio de una auditoría.'
        ],
        'Marketing' => [
            'Estamos esperando el feedback del focus group.',
            'El copy no tiene suficiente "punch".',
            'Facebook cambió el algoritmo otra vez.',
            'El presupuesto de ads se agotó.',
            'No es viral, es de nicho.',
            'El branding no está alineado.'
        ],
        'Ventas' => [
            'El cliente está de vacaciones.',
            'Están esperando el nuevo presupuesto fiscal.',
            'El contrato está en legal.',
            'Le gustó, pero quiere ver más opciones.',
            'Perdí la señal en el túnel.',
            'Estaba en una llamada con un lead importante.'
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES_WITH_EXCUSES as $categoryName => $excuses) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);

            foreach ($excuses as $excuseContent) {
                $excuse = new Excuse();
                $excuse->setContent($excuseContent);
                $excuse->setCategory($category);
                $manager->persist($excuse);
            }
        }

        $manager->flush();
    }
}
