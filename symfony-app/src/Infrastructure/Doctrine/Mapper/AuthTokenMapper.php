<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Model\AuthToken as DomainAuthToken;
use App\Infrastructure\Doctrine\Entity\AuthToken as EntityAuthToken;

final class AuthTokenMapper
{
    public static function toDomain(EntityAuthToken $entity): DomainAuthToken
    {
        return new DomainAuthToken(
            token: $entity->getToken(),
            user: UserMapper::toDomain($entity->getUser()),
            createdAt: \DateTimeImmutable::createFromInterface($entity->getCreatedAt()),
            id: $entity->getId()
        );
    }

    public static function toEntity(DomainAuthToken $model, ?EntityAuthToken $entity = null): EntityAuthToken
    {
        $entity = $entity ?? new EntityAuthToken();

        return $entity
            ->setToken($model->getToken())
            ->setCreatedAt($model->getCreatedAt());
    }
}
