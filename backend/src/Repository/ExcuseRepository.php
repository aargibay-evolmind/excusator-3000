<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Excuse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Excuse>
 *
 * @method Excuse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Excuse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Excuse[]    findAll()
 * @method Excuse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExcuseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excuse::class);
    }

    /**
     * Finds a random excuse for a specific category.
     */
    public function findRandomByCategory(int $categoryId): ?Excuse
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.category = :categoryId')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('categoryId', $categoryId);

        $excuses = $qb->getQuery()->getResult();

        if (count($excuses) === 0) {
            return null;
        }

        return $excuses[array_rand($excuses)];
    }
}
