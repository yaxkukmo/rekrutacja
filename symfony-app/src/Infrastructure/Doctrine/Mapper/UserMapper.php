<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Model\User as DomainUser;
use App\Infrastructure\Doctrine\Entity\User as EntityUser;

final class UserMapper
{
    public static function toDomain(EntityUser $entity): DomainUser
    {
        return new DomainUser(
            username: $entity->getUsername(),
            email: $entity->getEmail(),
            id: $entity->getId(),
            name: $entity->getName(),
            lastName: $entity->getLastName(),
            age: $entity->getAge(),
            bio: $entity->getBio()
        );
    }

    public static function toEntity(DomainUser $model, ?EntityUser $entity = null): EntityUser
    {
        $entity = $entity ?? new EntityUser();

        return $entity
            ->setUsername($model->getUsername())
            ->setEmail($model->getEmail())
            ->setName($model->getName())
            ->setLastName($model->getLastName())
            ->setAge($model->getAge())
            ->setBio($model->getBio());
    }
}
