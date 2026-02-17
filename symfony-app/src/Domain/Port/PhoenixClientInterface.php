<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface PhoenixClientInterface
{
    /** @return array<mixed> */
    public function fetchPhotos(string $token): array;
}
