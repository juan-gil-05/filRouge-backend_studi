<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618213042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD client_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C8495519EB6921 ON reservation (client_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_42C8495519EB6921 ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP client_id
        SQL);
    }
}
