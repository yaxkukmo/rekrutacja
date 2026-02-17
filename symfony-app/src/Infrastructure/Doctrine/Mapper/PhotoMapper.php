<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Model\Photo as DomainPhoto;
use App\Infrastructure\Doctrine\Entity\Photo as EntityPhoto;

final class PhotoMapper
{
    public static function toDomain(EntityPhoto $entity): DomainPhoto
    {
        return new DomainPhoto(
            imageUrl: $entity->getImageUrl(),
            user: UserMapper::toDomain($entity->getUser()),
            id: $entity->getId(),
            location: $entity->getLocation(),
            description: $entity->getDescription(),
            camera: $entity->getCamera(),
            takenAt: $entity->getTakenAt(),
            likeCounter: $entity->getLikeCounter()
        );
    }

    public static function toEntity(DomainPhoto $model, ?EntityPhoto $entity = null): EntityPhoto
    {
        $entity = $entity ?? new EntityPhoto();

        return $entity
            ->setImageUrl($model->getImageUrl())
            ->setLocation($model->getLocation())
            ->setDescription($model->getDescription())
            ->setCamera($model->getCamera())
            ->setTakenAt($model->getTakenAt())
            ->setLikeCounter($model->getLikeCounter());
    }
}
