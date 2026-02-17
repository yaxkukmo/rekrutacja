<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class Like
{
    public function __construct(
        private User $user,
        private Photo $photo,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        private ?int $id = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPhoto(): Photo
    {
        return $this->photo;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
