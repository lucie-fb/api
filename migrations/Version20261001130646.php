<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261001130646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trip (created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, departure_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration INT NOT NULL, price INT NOT NULL, catapult_model VARCHAR(255) NOT NULL, boarding_info TEXT NOT NULL, created_by_id UUID DEFAULT NULL, updated_by_id UUID DEFAULT NULL, deleted_by_id UUID DEFAULT NULL, origin_city_id UUID NOT NULL, destination_city_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7656F53BB03A8386 ON trip (created_by_id)');
        $this->addSql('CREATE INDEX IDX_7656F53B896DBBDE ON trip (updated_by_id)');
        $this->addSql('CREATE INDEX IDX_7656F53BC76F1F52 ON trip (deleted_by_id)');
        $this->addSql('CREATE INDEX IDX_7656F53B3EDB77C2 ON trip (origin_city_id)');
        $this->addSql('CREATE INDEX IDX_7656F53BE5955DD7 ON trip (destination_city_id)');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53B3EDB77C2 FOREIGN KEY (origin_city_id) REFERENCES city (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE trip ADD CONSTRAINT FK_7656F53BE5955DD7 FOREIGN KEY (destination_city_id) REFERENCES city (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trip DROP CONSTRAINT FK_7656F53BB03A8386');
        $this->addSql('ALTER TABLE trip DROP CONSTRAINT FK_7656F53B896DBBDE');
        $this->addSql('ALTER TABLE trip DROP CONSTRAINT FK_7656F53BC76F1F52');
        $this->addSql('ALTER TABLE trip DROP CONSTRAINT FK_7656F53B3EDB77C2');
        $this->addSql('ALTER TABLE trip DROP CONSTRAINT FK_7656F53BE5955DD7');
        $this->addSql('DROP TABLE trip');
    }
}
