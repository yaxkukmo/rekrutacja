<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\User;
use App\Domain\Port\AuthTokenRepositoryInterface;
use App\Domain\Port\UserRepositoryInterface;

final class AuthService
{
    public function __construct(
        private AuthTokenRepositoryInterface $authTokenRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function authenticate(string $username, string $token): User
    {
        $authToken = $this->authTokenRepository->findByToken($token);

        if (!$authToken) {
            throw new \DomainException('Invalid token');
        }

        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            throw new \DomainException('User not found');
        }

        return $user;
    }
}
