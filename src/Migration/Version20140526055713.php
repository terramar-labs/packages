<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @generated 2014-05-26 05:57:13
 */
class Version20140526055713 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite");
        
        $this->addSql("CREATE TABLE configurations (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, token VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE packages (id INTEGER NOT NULL, configuration_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, external_id VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, ssh_url VARCHAR(255) NOT NULL, web_url VARCHAR(255) NOT NULL, fqn VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_9BB5C0A773F32DD8 ON packages (configuration_id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite");
        
        $this->addSql("DROP TABLE configurations");
        $this->addSql("DROP TABLE packages");
    }
}
