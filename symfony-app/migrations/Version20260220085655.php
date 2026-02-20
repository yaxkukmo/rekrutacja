<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220085655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add composite index on photos(user_id, image_url) for duplicate detection';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_photos_user_id_image_url ON photos (user_id, image_url)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_photos_user_id_image_url');
    }
}
