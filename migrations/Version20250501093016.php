<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501093016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vente ADD somme_investis DOUBLE PRECISION NOT NULL, ADD prix_cours_achat DOUBLE PRECISION NOT NULL, ADD date_achat DATETIME NOT NULL, ADD prix_cours_vente DOUBLE PRECISION NOT NULL, ADD effet_levier INT NOT NULL, DROP gppourcentage, DROP somme_totale
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vente ADD gppourcentage DOUBLE PRECISION NOT NULL, ADD somme_totale DOUBLE PRECISION NOT NULL, DROP somme_investis, DROP prix_cours_achat, DROP date_achat, DROP prix_cours_vente, DROP effet_levier
        SQL);
    }
}
