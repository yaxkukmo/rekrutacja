<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Model\Like;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\LikeRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\Like as LikeEntity;
use App\Infrastructure\Doctrine\Entity\Photo as PhotoEntity;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;
use App\Infrastructure\Doctrine\Mapper\LikeMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class LikeRepository extends ServiceEntityRepository implements LikeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikeEntity::class);
    }

    public function hasUserLikedPhoto(User $user, Photo $photo): bool
    {
        $count = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user = :userId')
            ->andWhere('l.photo = :photoId')
            ->setParameter('userId', $user->getId())
            ->setParameter('photoId', $photo->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function createLike(User $user, Photo $photo): Like
    {
        $em = $this->getEntityManager();

        $userEntity = $em->getReference(UserEntity::class, $user->getId());
        $photoEntity = $em->getReference(PhotoEntity::class, $photo->getId());

        $likeEntity = new LikeEntity();
        $likeEntity->setUser($userEntity);
        $likeEntity->setPhoto($photoEntity);

        $em->persist($likeEntity);
        $em->flush();

        return LikeMapper::toDomain($likeEntity);
    }

    public function removeLike(User $user, Photo $photo): void
    {
        $em = $this->getEntityManager();

        $likeEntity = $this->createQueryBuilder('l')
            ->where('l.user = :userId')
            ->andWhere('l.photo = :photoId')
            ->setParameter('userId', $user->getId())
            ->setParameter('photoId', $photo->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($likeEntity) {
            $em->remove($likeEntity);
            $em->flush();
        }
    }

    public function incrementPhotoLikes(Photo $photo): void
    {
        $this->updatePhotoLikes($photo, 1);
    }

    public function decrementPhotoLikes(Photo $photo): void
    {
        $this->updatePhotoLikes($photo, -1);
    }

    private function updatePhotoLikes(Photo $photo, int $increment): void
    {
        $em = $this->getEntityManager();
        $photoEntity = $em->getReference(PhotoEntity::class, $photo->getId());
        $photoEntity->setLikeCounter($photoEntity->getLikeCounter() + $increment);
        $em->flush();
    }
}
