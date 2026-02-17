<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class AuthToken
{
    public function __construct(
        private string $token,
        private User $user,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        private ?int $id = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
