<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\LikeRepositoryInterface;

final class LikeService
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository
    ) {}

    public function likePhoto(User $user, Photo $photo): void
    {
        if ($this->likeRepository->hasUserLikedPhoto($user, $photo)) {
            throw new \DomainException('Already liked');
        }

        $this->likeRepository->createLike($user, $photo);
        $photo->like();
        $this->likeRepository->incrementPhotoLikes($photo);
    }

    public function unlikePhoto(User $user, Photo $photo): void
    {
        if (!$this->likeRepository->hasUserLikedPhoto($user, $photo)) {
            throw new \DomainException('Not liked yet');
        }

        $this->likeRepository->removeLike($user, $photo);
        $photo->unlike();
        $this->likeRepository->decrementPhotoLikes($photo);
    }

    public function hasUserLikedPhoto(User $user, Photo $photo): bool
    {
        return $this->likeRepository->hasUserLikedPhoto($user, $photo);
    }
}
