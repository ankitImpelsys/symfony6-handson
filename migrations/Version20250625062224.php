<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625062224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE userprofile ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE userprofile ADD CONSTRAINT FK_1D3656B1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D3656B1A76ED395 ON userprofile (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE userprofile DROP FOREIGN KEY FK_1D3656B1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1D3656B1A76ED395 ON userprofile
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE userprofile DROP user_id
        SQL);
    }
}
