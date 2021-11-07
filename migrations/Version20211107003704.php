<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211107003704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stop_time (id INT AUTO_INCREMENT NOT NULL, trip_id INT NOT NULL, stop_id INT NOT NULL, gtfs_trip_id VARCHAR(255) NOT NULL, arrival_time TIME NOT NULL, departure_time TIME NOT NULL, gtfs_stop_id VARCHAR(255) NOT NULL, stop_sequence INT NOT NULL, INDEX IDX_85725A5AA5BC2E0E (trip_id), INDEX IDX_85725A5A3902063D (stop_id), INDEX time_trip_id (gtfs_trip_id), INDEX time_stop_id (gtfs_stop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stop_time ADD CONSTRAINT FK_85725A5AA5BC2E0E FOREIGN KEY (trip_id) REFERENCES trip (id)');
        $this->addSql('ALTER TABLE stop_time ADD CONSTRAINT FK_85725A5A3902063D FOREIGN KEY (stop_id) REFERENCES stop (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE stop_time');
    }
}
