<?php

namespace AlphaSoft\AsLinkOrm\Coordinator;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;

final readonly class EntityRelationCoordinator
{
    public function __construct(private EntityManager $entityManager)
    {
    }

    public function hasOne(string $relatedModel, array $criteria = []): ?object
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }

        return $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findOneBy($criteria);
    }

    public function hasMany(string $relatedModel, array $criteria = []): \SplObjectStorage
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }

        return $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findBy($criteria);
    }

    private function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
