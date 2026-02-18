<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\User;
use App\Domain\Port\UserRepositoryInterface;

final class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getCurrentUser(?int $userId): ?User
    {
        if (!$userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }
}
