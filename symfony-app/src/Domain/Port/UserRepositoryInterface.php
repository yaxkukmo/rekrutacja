<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;
}
