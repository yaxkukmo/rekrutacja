<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Model\User;
use App\Domain\Port\UserRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;
use App\Infrastructure\Doctrine\Mapper\UserMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEntity::class);
    }

    public function findById(int $id): ?User
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        return UserMapper::toDomain($entity);
    }

    public function findByUsername(string $username): ?User
    {
        $entity = $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        return UserMapper::toDomain($entity);
    }
}
