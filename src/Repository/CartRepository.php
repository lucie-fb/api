<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Enum\CartStatus;
use App\Entity\User;
use App\Trait\EntityRepositorySaverTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    use EntityRepositorySaverTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * Returns the pending cart owned by this user, if any.
     */
    public function findActiveFor(User $user): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.createdBy = :user')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', CartStatus::Pending)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
