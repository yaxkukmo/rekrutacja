<?php

declare(strict_types=1);

namespace App\Domain\Model;

use DateTimeImmutable;

final class PhotoFilter
{
    public function __construct(
        private ?string $location = null,
        private ?string $camera = null,
        private ?string $description = null,
        private ?string $username = null,
        private ?\DateTimeImmutable $takenFrom = null,
        private ?\DateTimeImmutable $takenTo = null
    ) { }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getCamera(): ?string
    {
        return $this->camera;
    }

    public function getDescription(): ?string 
    {
        return $this->description;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getTakenAt(): ?DateTimeImmutable
    {
        return $this->takenFrom;
    }

    public function getTakenTo(): ?DateTimeImmutable
    {
        return $this->takenTo;
    }

    public function isEmpty(): bool 
    {
        return $this->location === null
            && $this->camera === null
            && $this->description === null
            && $this->username === null
            && $this->takenFrom === null
            && $this->takenAt === null;
    }
}
