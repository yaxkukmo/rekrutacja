<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Model\Like as DomainLike;
use App\Infrastructure\Doctrine\Entity\Like as EntityLike;

final class LikeMapper
{
    public static function toDomain(EntityLike $entity): DomainLike
    {
        return new DomainLike(
            user: UserMapper::toDomain($entity->getUser()),
            photo: PhotoMapper::toDomain($entity->getPhoto()),
            createdAt: \DateTimeImmutable::createFromInterface($entity->getCreatedAt()),
            id: $entity->getId()
        );
    }

    public static function toEntity(DomainLike $model, ?EntityLike $entity = null): EntityLike
    {
        $entity = $entity ?? new EntityLike();

        return $entity
            ->setCreatedAt($model->getCreatedAt());
    }
}
