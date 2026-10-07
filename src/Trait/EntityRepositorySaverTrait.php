<?php

namespace App\Trait;

trait EntityRepositorySaverTrait
{
    /**
     * Schedules an entity for insertion or update at the next flush.
     */
    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * Writes every scheduled change to the database.
     */
    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
