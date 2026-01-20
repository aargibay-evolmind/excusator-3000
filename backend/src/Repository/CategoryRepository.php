<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Finds active categories (not deleted) that have at least 5 excuses (also not deleted).
     *
     * @return Category[]
     */
    public function findActiveWithMinExcuses(int $minExcuses = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.excuses', 'e')
            ->where('c.deletedAt IS NULL')
            ->andWhere('e.deletedAt IS NULL')
            ->groupBy('c.id')
            ->having('COUNT(e.id) >= :minExcuses')
            ->setParameter('minExcuses', $minExcuses)
            ->getQuery()
            ->getResult();
    }
}
