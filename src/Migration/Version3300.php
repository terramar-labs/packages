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
 * Migration script for 3.x->3.3.
 *
 * Only necessary if upgrading to 3.3.
 *
 * @version 3.3.0
 */
class Version3300 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('packages_bitbucket_remotes');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('remote_id', 'integer');
        $table->addColumn('token', 'string');
        $table->addColumn('username', 'string');
        $table->addColumn('account', 'string');
        $table->addForeignKeyConstraint('remotes', ['remote_id'], ['id']);

        $table = $schema->createTable('packages_bitbucket_configurations');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('package_id', 'integer');
        $table->addForeignKeyConstraint('packages', ['package_id'], ['id']);
    }

    public function postUp(Schema $schema)
    {
        $this->connection->executeQuery('INSERT INTO packages_bitbucket_remotes (remote_id, enabled, token, username, account) SELECT id, 0, "", "", "" FROM remotes');
        $this->connection->executeQuery('INSERT INTO packages_bitbucket_configurations (package_id, enabled) SELECT id, 0 FROM packages');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('packages_bitbucket_remotes');
        $schema->dropTable('packages_bitbucket_configurations');
    }
}
