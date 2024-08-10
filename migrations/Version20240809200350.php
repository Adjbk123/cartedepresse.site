<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809200350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE duplicata_demande (id INT AUTO_INCREMENT NOT NULL, demande_id INT NOT NULL, declaration_perte VARCHAR(255) NOT NULL, cip VARCHAR(255) NOT NULL, INDEX IDX_8FD55D980E95E18 (demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE duplicata_demande ADD CONSTRAINT FK_8FD55D980E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE duplicata_demande DROP FOREIGN KEY FK_8FD55D980E95E18');
        $this->addSql('DROP TABLE duplicata_demande');
    }
}
