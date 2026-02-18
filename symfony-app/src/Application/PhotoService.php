<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\PhotoRepositoryInterface;

final class PhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $photoRepository,
        private LikeService $likeService
    ) {}

    /** @return array{photos: Photo[], userLikes: array<int, bool>} */
    public function getPhotosWithLikeStatus(?User $user): array
    {
        $photos = $this->photoRepository->findAllWithUsers();
        $userLikes = [];

        if ($user) {
            foreach ($photos as $photo) {
                $userLikes[$photo->getId()] = $this->likeService->hasUserLikedPhoto($user, $photo);
            }
        }

        return ['photos' => $photos, 'userLikes' => $userLikes];
    }

    public function getPhotoById(int $id): Photo
    {
        $photo = $this->photoRepository->findById($id);

        if (!$photo) {
            throw new \DomainException('Photo not found');
        }

        return $photo;
    }

    public function countUserPhotos(int $userId): int
    {
        return $this->photoRepository->countByUserId($userId);
    }
}
