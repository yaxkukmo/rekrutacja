<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Port\PhoenixClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixClient implements PhoenixClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $phoenixBaseUrl
    ) {}

    /** @return array<mixed> */
    public function fetchPhotos(string $token): array
    {
        $response = $this->httpClient->request('GET', $this->phoenixBaseUrl . '/api/photos', [
            'headers' => [
                'access-token' => $token,
            ],
        ]);

        $data = $response->toArray();

        return $data['photos'] ?? [];
    }
}
