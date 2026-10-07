<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Trip;
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

    /**
     * Returns the trips flying the given route on the given day, ordered by departure time.
     *
     * @return Trip[]
     */
    public function search(City $origin, City $destination, \DateTimeImmutable $day): array
    {
        // égalité de date impossible : departureAt porte une heure, on borne donc sur l'intervalle du jour
        $start = $day->setTime(0, 0);
        $end = $start->modify('+1 day');

        return $this->createQueryBuilder('t')
            ->andWhere('t.origin = :origin')
            ->andWhere('t.destination = :destination')
            ->andWhere('t.departureAt >= :start')
            ->andWhere('t.departureAt < :end')
            ->setParameter('origin', $origin)
            ->setParameter('destination', $destination)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.departureAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
