<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240709025444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_organe_professionnel ADD organe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE historique_organe_professionnel ADD CONSTRAINT FK_D67D9565B5E5B09D FOREIGN KEY (organe_id) REFERENCES organe (id)');
        $this->addSql('CREATE INDEX IDX_D67D9565B5E5B09D ON historique_organe_professionnel (organe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_organe_professionnel DROP FOREIGN KEY FK_D67D9565B5E5B09D');
        $this->addSql('DROP INDEX IDX_D67D9565B5E5B09D ON historique_organe_professionnel');
        $this->addSql('ALTER TABLE historique_organe_professionnel DROP organe_id');
    }
}
