<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130728025111 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Post ADD account_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Post ADD CONSTRAINT FK_FAB8C3B39B6B5FBA FOREIGN KEY (account_id) REFERENCES Users (id)");
        $this->addSql("CREATE INDEX IDX_FAB8C3B39B6B5FBA ON Post (account_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Post DROP FOREIGN KEY FK_FAB8C3B39B6B5FBA");
        $this->addSql("DROP INDEX IDX_FAB8C3B39B6B5FBA ON Post");
        $this->addSql("ALTER TABLE Post DROP account_id");
    }
}
