<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class User
{
    public function __construct(
        private string $username,
        private string $email,
        private ?int $id = null,
        private ?string $name = null,
        private ?string $lastName = null,
        private ?int $age = null,
        private ?string $bio = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }
}
