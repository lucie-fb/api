<?php

namespace App\Service\Utils;

use App\Entity\Impl\AbstractEntity;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class AuditService
{
    // Security est injecté : c'est lui qui sait qui porte la requête en cours
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Stamps an entity as created now, by the current user when there is one.
     */
    public function stampCreation(AbstractEntity $entity): void
    {
        $entity->setCreatedAt(new \DateTimeImmutable());
        $entity->setCreatedBy($this->currentUser());
    }

    /**
     * Stamps an entity as updated now, by the current user when there is one.
     */
    public function stampUpdate(AbstractEntity $entity): void
    {
        $entity->setUpdatedAt(new \DateTimeImmutable());
        $entity->setUpdatedBy($this->currentUser());
    }

    /**
     * Marks an entity as deleted now, by the current user when there is one.
     */
    public function markDeleted(AbstractEntity $entity): void
    {
        $entity->setDeletedAt(new \DateTimeImmutable());
        $entity->setDeletedBy($this->currentUser());
    }

    /**
     * Returns the authenticated user, or null outside an authenticated request.
     */
    private function currentUser(): ?User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : null;
    }
}
