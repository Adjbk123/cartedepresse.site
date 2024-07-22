<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711180324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande ADD agent_traitant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A543627A0D FOREIGN KEY (agent_traitant_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2694D7A543627A0D ON demande (agent_traitant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A543627A0D');
        $this->addSql('DROP INDEX IDX_2694D7A543627A0D ON demande');
        $this->addSql('ALTER TABLE demande DROP agent_traitant_id');
    }
}
