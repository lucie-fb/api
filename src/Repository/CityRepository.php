<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Returns cities ordered by name, optionally filtered on a case-insensitive substring.
     *
     * @return City[]
     */
    public function search(?string $q, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit);

        if (null !== $q) {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:pattern)')
                ->setParameter('pattern', '%'.$q.'%');
        }

        return $qb->getQuery()->getResult();
    }
}
