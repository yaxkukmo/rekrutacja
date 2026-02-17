<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Model\AuthToken;
use App\Domain\Port\AuthTokenRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\AuthToken as AuthTokenEntity;
use App\Infrastructure\Doctrine\Mapper\AuthTokenMapper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class AuthTokenRepository extends ServiceEntityRepository implements AuthTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthTokenEntity::class);
    }

    public function findByToken(string $token): ?AuthToken
    {
        $entity = $this->createQueryBuilder('t')
            ->where('t.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        return AuthTokenMapper::toDomain($entity);
    }
}
