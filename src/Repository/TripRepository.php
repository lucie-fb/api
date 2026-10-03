<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    /** @return Trip[]
     * @throws \DateMalformedStringException
     */
    public function search(City $origin, City $destination, \DateTimeImmutable $day): array{

        $start = $day->setTime(0, 0);
        $end = $start->modify('+1 day');
        return $this->createQueryBuilder('t')
            ->andWhere('t.origin = :origin')
            ->setParameter('origin', $origin)
            ->andWhere('t.destination = :destination')
            ->setParameter('destination', $destination)
            ->andWhere('t.departureAt >= :start')
            ->setParameter('start', $start)
            ->andWhere('t.departureAt <= :end')
            ->setParameter('end', $end)
            ->orderBy('t.departureAt', 'ASC')
            ->getQuery()
            ->getResult();


    }

}
