<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711195954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande ADD historique_organe_actuel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5FD67B9FD FOREIGN KEY (historique_organe_actuel_id) REFERENCES historique_organe_professionnel (id)');
        $this->addSql('CREATE INDEX IDX_2694D7A5FD67B9FD ON demande (historique_organe_actuel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5FD67B9FD');
        $this->addSql('DROP INDEX IDX_2694D7A5FD67B9FD ON demande');
        $this->addSql('ALTER TABLE demande DROP historique_organe_actuel_id');
    }
}
