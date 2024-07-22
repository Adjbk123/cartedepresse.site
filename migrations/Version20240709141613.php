<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240709141613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piece_jointe ADD type_piece_id INT DEFAULT NULL, DROP type_piece');
        $this->addSql('ALTER TABLE piece_jointe ADD CONSTRAINT FK_AB5111D49F0E854 FOREIGN KEY (type_piece_id) REFERENCES type_piece (id)');
        $this->addSql('CREATE INDEX IDX_AB5111D49F0E854 ON piece_jointe (type_piece_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piece_jointe DROP FOREIGN KEY FK_AB5111D49F0E854');
        $this->addSql('DROP INDEX IDX_AB5111D49F0E854 ON piece_jointe');
        $this->addSql('ALTER TABLE piece_jointe ADD type_piece VARCHAR(255) NOT NULL, DROP type_piece_id');
    }
}
