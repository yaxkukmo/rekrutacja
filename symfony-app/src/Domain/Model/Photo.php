<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class Photo
{
    public function __construct(
        private string $imageUrl,
        private User $user,
        private ?int $id = null,
        private ?string $location = null,
        private ?string $description = null,
        private ?string $camera = null,
        private ?\DateTimeImmutable $takenAt = null,
        private int $likeCounter = 0
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCamera(): ?string
    {
        return $this->camera;
    }

    public function getTakenAt(): ?\DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLikeCounter(): int
    {
        return $this->likeCounter;
    }

    public function like(): void
    {
        $this->likeCounter++;
    }

    public function unlike(): void
    {
        if ($this->likeCounter <= 0) {
            throw new \DomainException('Like counter cannot be negative');
        }
        $this->likeCounter--;
    }
}
