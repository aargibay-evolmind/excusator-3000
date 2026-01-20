<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Excuse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const CATEGORY_NAMES = [
        'Desarrolladores',
        'Diseñadores',
        'Project Managers',
        'SysAdmins',
        'Testers',
        'Recursos Humanos',
        'Marketing',
        'Ventas'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORY_NAMES as $index => $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);

            for ($i = 1; $i <= 10; $i++) {
                $excuse = new Excuse();
                $excuse->setContent("Excusa #$i para $name: No fue culpa mía, fue del " . ($i % 2 === 0 ? "usuario" : "sistema") . ".");
                $excuse->setCategory($category);
                $manager->persist($excuse);
            }
        }

        $manager->flush();
    }
}
