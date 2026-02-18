<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260218120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique index on likes (user_id, photo_id) to prevent duplicate likes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM likes l1 USING likes l2 WHERE l1.id > l2.id AND l1.user_id = l2.user_id AND l1.photo_id = l2.photo_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_likes_user_photo ON likes(user_id, photo_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_likes_user_photo');
    }
}
