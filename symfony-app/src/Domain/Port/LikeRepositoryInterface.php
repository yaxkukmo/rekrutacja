<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Like;
use App\Domain\Model\Photo;
use App\Domain\Model\User;

interface LikeRepositoryInterface
{
    public function hasUserLikedPhoto(User $user, Photo $photo): bool;

    public function createLike(User $user, Photo $photo): Like;

    public function removeLike(User $user, Photo $photo): void;

    public function incrementPhotoLikes(Photo $photo): void;

    public function decrementPhotoLikes(Photo $photo): void;
}
