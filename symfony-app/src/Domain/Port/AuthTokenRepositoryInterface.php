<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\AuthToken;

interface AuthTokenRepositoryInterface
{
    public function findByToken(string $token): ?AuthToken;
}
