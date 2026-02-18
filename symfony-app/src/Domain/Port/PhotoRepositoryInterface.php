<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;

interface PhotoRepositoryInterface
{
    public function findById(int $id): ?Photo;

    /** @return Photo[] */
    public function findAllWithUsers(): array;

    public function countByUserId(int $userId): int;
}
