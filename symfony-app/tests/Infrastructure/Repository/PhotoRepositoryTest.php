<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Domain\Model\PhotoFilter;
use App\Domain\Port\PhotoRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhotoRepositoryTest extends KernelTestCase
{
    private PhotoRepositoryInterface $repository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(PhotoRepositoryInterface::class);
    }

    public function testFindByFilterLocation():void
    {
        $filter = new PhotoFilter(location: 'Swiss');
        $photos = $this->repository->findByFilter($filter);

        $this->assertNotEmpty($photos);
        foreach ($photos as $photo) {
            $this->assertStringContainsString('Swiss', $photo->getLocation());
        }
    }

    public function testFindByFilterReturnsEmptyForNonexistentLocation(): void
    {
        $filter = new PhotoFilter(location: 'nonexistentLocation');
        $photos = $this->repository->findByFilter($filter);

        $this->assertEmpty($photos);
    }
}
