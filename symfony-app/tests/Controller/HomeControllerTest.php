<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testItShowsPhotos(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-card');
    }

    public function testItFiltersPhotosByDateRange(): void
    {
        $client = static::createClient();
        $client->request('GET', '/?takenFrom=2024-01-01&takenTo=2024-06-30');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(7, '.photo-card');
    }

    public function testItShowsNoPhotosOutsideDateRange(): void
    {
        $client = static::createClient();
        $client->request('GET', '/?takenFrom=2026-01-01&takenTo=2026-06-30');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(0, '.photo-card');
    }
}
