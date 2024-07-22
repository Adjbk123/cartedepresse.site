<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708205718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE historique_organe_professionnel (id INT AUTO_INCREMENT NOT NULL, professionnel_id INT DEFAULT NULL, profession_id INT DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, INDEX IDX_D67D95658A49CC82 (professionnel_id), INDEX IDX_D67D9565FDEF8996 (profession_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profession (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historique_organe_professionnel ADD CONSTRAINT FK_D67D95658A49CC82 FOREIGN KEY (professionnel_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE historique_organe_professionnel ADD CONSTRAINT FK_D67D9565FDEF8996 FOREIGN KEY (profession_id) REFERENCES profession (id)');
        $this->addSql('ALTER TABLE user ADD npi INT DEFAULT NULL, ADD sexe VARCHAR(255) DEFAULT NULL, ADD nationalite VARCHAR(255) DEFAULT NULL, ADD photo VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_organe_professionnel DROP FOREIGN KEY FK_D67D95658A49CC82');
        $this->addSql('ALTER TABLE historique_organe_professionnel DROP FOREIGN KEY FK_D67D9565FDEF8996');
        $this->addSql('DROP TABLE historique_organe_professionnel');
        $this->addSql('DROP TABLE profession');
        $this->addSql('ALTER TABLE user DROP npi, DROP sexe, DROP nationalite, DROP photo');
    }
}
