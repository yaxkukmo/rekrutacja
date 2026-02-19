<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Model\Photo;
use App\Domain\Model\PhotoFilter;
use App\Domain\Port\PhotoRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\Photo as PhotoEntity;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;
use App\Infrastructure\Doctrine\Mapper\PhotoMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PhotoRepository extends ServiceEntityRepository implements PhotoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhotoEntity::class);
    }

    public function findById(int $id): ?Photo
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        return PhotoMapper::toDomain($entity);
    }

    /** @return Photo[] */
    public function findAllWithUsers(): array
    {
        $entities = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn(PhotoEntity $entity) => PhotoMapper::toDomain($entity),
            $entities
        );
    }

    / ** @return Photo[] */
    public function findByFilter(PhotoFilter $filter): array
    {
        
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC');

        if ($filter->getLocation()) {
            $queryBuilder->andWhere('p.location LIKE :location')
                ->setParameter('location', '%' . $filter->getLocation() . "%");    
        }

        if ($filter->getCamera()) {
            $queryBuilder->andWhere('p.camera LIKE :camera')
                ->setParameter('camera', '%' . $filter->getCamera() . '%');
        }

        if ($filter->getUsername()) {
            $queryBuilder->andWhere('u.username = :username')
                ->setParameter('username', $filter->getUsername());
        }

        if ($filter->getDescription()) {
            $queryBuilder->andWhere('p.description LIKE :description')
                ->setParameter('description', '%' . $filter->getDescription() . '%');
        }

        if ($filter->getTakenFrom()) {
            $queryBuilder->andWhere('p.takenAt >= :dateFrom')
                ->setParameter('dateFrom', $filter->getTakenFrom());
        }

        if ($filter->getTakenTo()) {
            $queryBuilder->andWhere('p.takenAt <= :dateTo')
                ->setParameter('dateTo', $filter->getTakenTo());
        }

        $entities = $queryBuilder->getQuery()
            ->getResult();

        return array_map(
            fn(PhotoEntity $entity) => PhotoMapper::toDomain($entity),
            $entities
        );
    }
                                 

    public function countByUserId(int $userId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function saveAll(array $photos): void
    {
        $em = $this->getEntityManager();
        foreach($photos as $photo) {
            $entity = PhotoMapper::toEntity($photo);
            $entity->setUser($em->getReference(UserEntity::class, $photo->getUser()->getId()));
            $em->persist($entity);
        }
        $em->flush();
    }

    public function existsByImageUrl(int $userId, string $imageUrl): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user = :userId')
            ->andWhere('p.imageUrl = :imageUrl')
            ->setParameter('userId', $userId)
            ->setParameter('imageUrl', $imageUrl)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
