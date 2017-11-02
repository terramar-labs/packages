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
 * Migration script for 3.0->3.1.
 *
 * Only necessary if upgrading from 3.0 to 3.1.
 *
 * @version 3.1.0
 */
class Version3100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('packages_sami_configurations');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('package_id', 'integer');
        $table->addColumn('docs_path', 'string');
        $table->addColumn('repo_path', 'string');
        $table->addColumn('remote_repo_path', 'string');
        $table->addColumn('title', 'string');
        $table->addColumn('theme', 'string');
        $table->addColumn('tags', 'string');
        $table->addColumn('refs', 'string');
        $table->addColumn('templates_dir', 'string');
        $table->addForeignKeyConstraint('packages', ['package_id'], ['id']);

        $table = $schema->createTable('packages_cloneproject_configurations');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('package_id', 'integer');
        $table->addForeignKeyConstraint('packages', ['package_id'], ['id']);
    }

    public function postUp(Schema $schema)
    {
        $this->connection->executeQuery('INSERT INTO packages_sami_configurations (package_id, enabled, docs_path, repo_path, remote_repo_path, title, theme, tags, refs, templates_dir) SELECT id, 0, "", "", "", "", "", "", "", "" FROM packages');
        $this->connection->executeQuery('INSERT INTO packages_cloneproject_configurations (package_id, enabled) SELECT id, 0 FROM packages');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('packages_sami_configurations');
        $schema->dropTable('packages_cloneproject_configurations');
    }
}
