<?php

namespace Terramar\Packages\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @generated 2014-05-26 06:33:34
 */
class Version20140526063334 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite");
        
        $this->addSql("CREATE TEMPORARY TABLE __temp__packages AS SELECT id, configuration_id, name, description, external_id, enabled, ssh_url, web_url, fqn, '' AS hook_external_id FROM packages");
        $this->addSql("DROP TABLE packages");
        $this->addSql("CREATE TABLE packages (id INTEGER NOT NULL, configuration_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, external_id VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, ssh_url VARCHAR(255) NOT NULL, web_url VARCHAR(255) NOT NULL, fqn VARCHAR(255) NOT NULL, hook_external_id VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_9BB5C0A773F32DD8 FOREIGN KEY (configuration_id) REFERENCES configurations (id) NOT DEFERRABLE INITIALLY IMMEDIATE)");
        $this->addSql("INSERT INTO packages (id, configuration_id, name, description, external_id, enabled, ssh_url, web_url, fqn, hook_external_id) SELECT id, configuration_id, name, description, external_id, enabled, ssh_url, web_url, fqn, hook_external_id FROM __temp__packages");
        $this->addSql("DROP TABLE __temp__packages");
        $this->addSql("CREATE INDEX IDX_9BB5C0A773F32DD8 ON packages (configuration_id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite");
        
        $this->addSql("DROP INDEX IDX_9BB5C0A773F32DD8");
        $this->addSql("CREATE TEMPORARY TABLE __temp__packages AS SELECT id, configuration_id, name, external_id, hook_external_id, description, enabled, ssh_url, web_url, fqn FROM packages");
        $this->addSql("DROP TABLE packages");
        $this->addSql("CREATE TABLE packages (id INTEGER NOT NULL, configuration_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, external_id VARCHAR(255) NOT NULL, hook_external_id VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, ssh_url VARCHAR(255) NOT NULL, web_url VARCHAR(255) NOT NULL, fqn VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("INSERT INTO packages (id, configuration_id, name, external_id, hook_external_id, description, enabled, ssh_url, web_url, fqn) SELECT id, configuration_id, name, external_id, hook_external_id, description, enabled, ssh_url, web_url, fqn FROM __temp__packages");
        $this->addSql("DROP TABLE __temp__packages");
    }
}
