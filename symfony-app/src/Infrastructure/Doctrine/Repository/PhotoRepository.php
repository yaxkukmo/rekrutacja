<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Model\Photo;
use App\Domain\Port\PhotoRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\Photo as PhotoEntity;
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

    public function countByUserId(int $userId): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
