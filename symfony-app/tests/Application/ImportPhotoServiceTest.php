<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\ImportPhotoService;
use App\Domain\Model\User;
use App\Domain\Port\PhoenixClientInterface;
use App\Domain\Port\PhotoRepositoryInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

class ImportPhotoServiceTest extends TestCase
{
    public function testItThrowsExceptionWhenUserHasNoToken() {
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $user = new User(id: 1, username: 'testuser', email: 'some@email.uk');

        $service = new ImportPhotoService($photoRepository, $phoenixClient);
        $this->expectException(DomainException::class);
        $service->importPhotos($user);
    }

    public function testItCallsPhoenixApiAndSavePhotos() {
        $data = [
            ['photo_url' => 'http://domain.uk/examplePhoto.png'], 
            ['photo_url' => 'http://domain.uk/examplePhoto2.png']
        ];

        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $phoenixClient->method('fetchPhotos')->willReturn($data);

        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoRepository->method('existsByImageUrl')->willReturn(false);
        $photoRepository->expects($this->once())->method('saveAll');

        $user = new User(id: 1, username: 'testuser', email: 'some@email.uk', phoenixApiToken: 'token');

        $service = new ImportPhotoService($photoRepository, $phoenixClient);
        $result = $service->importPhotos($user);

        $this->assertEquals(2, $result);
    }

    public function testItOmitsDuplicates() {
        $data = [
            ['photo_url' => 'http://domain.uk/examplePhoto.png'], 
            ['photo_url' => 'http://domain.uk/examplePhoto2.png']
        ];

        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $phoenixClient->method('fetchPhotos')->willReturn($data);

        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $photoRepository->method('existsByImageUrl')->willReturn(false, true);
        $photoRepository->expects($this->once())->method('saveAll');

        $user = new User(id: 1, username: 'testuser', email: 'some@email.uk', phoenixApiToken: 'token');

        $service = new ImportPhotoService($photoRepository, $phoenixClient);
        $result = $service->importPhotos($user);

        $this->assertEquals(1, $result);
    }

    public function testItThrowsExceptionWhenPhoenixApiReturnsError() {
        $user = new User(id: 1, username: 'testuser', email: 'some@email.uk', phoenixApiToken: 'token');
        $photoRepository = $this->createMock(PhotoRepositoryInterface::class);
        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $phoenixClient->method('fetchPhotos')->willThrowException(new \Exception());
        $this->expectException(\DomainException::class);
        $service = new ImportPhotoService($photoRepository, $phoenixClient);
        $service->importPhotos($user);
    }





}
