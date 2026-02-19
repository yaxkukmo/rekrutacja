<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\User;
use App\Domain\Model\Photo;
use App\Domain\Port\PhoenixClientInterface;
use App\Domain\Port\PhotoRepositoryInterface;
use DomainException;
use Exception;

final class ImportPhotoService
{
    public function __construct(
        private PhotoRepositoryInterface $photoRepository,
        private PhoenixClientInterface $phoenixClient
    ) { }

    public function importPhotos(User $user): int
    {
        $token = $user->getPhoenixApiToken();

        if (!$token) {
            throw new DomainException('Provided token is invalid');
        }

        try {
            $photosData = $this->fetchFromPhoenix($token);
        } catch (\Exception $exception) {
            throw new DomainException('Cannot fetch photos');

        }

        $filteredPhotos = $this->filterDuplicates($user, $photosData);

        if (!empty($filteredPhotos)) {
            $this->photoRepository->saveAll($filteredPhotos);
        }

        return count($filteredPhotos);
    }

    private function fetchFromPhoenix(string $token): array
    {
        return $this->phoenixClient->fetchPhotos($token);
    }

    private function filterDuplicates(User $user, array $photos): array
    {
        $filtered = [];
        foreach($photos as $photo) {
            if (!$this->photoRepository->existsByImageUrl($user->getId(), $photo['photo_url'])) {
                $filtered[] = new Photo(
                    imageUrl: $photo['photo_url'],
                    user: $user
                );
            }
        }
        return $filtered;
    }

}
