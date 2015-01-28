<?php

namespace Terramar\Packages\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @version 3.1.0
 */
class Version3100 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('packages_sami_configurations');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(array('id'));
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('package_id', 'integer');
        $table->addColumn('docs_path', 'string');
        $table->addColumn('repo_path', 'string');
        $table->addColumn('remote_repo_path', 'string');
        $table->addColumn('title', 'string');
        $table->addColumn('theme', 'string');
        $table->addColumn('tags', 'string');
        $table->addColumn('refs', 'string');
        $table->addForeignKeyConstraint('packages', array('package_id'), array('id'));

        $table = $schema->createTable('packages_cloneproject_configurations');
        $table->addColumn('id', 'integer');
        $table->setPrimaryKey(array('id'));
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('package_id', 'integer');
        $table->addForeignKeyConstraint('packages', array('package_id'), array('id'));
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('packages_sami_configurations');
        $schema->dropTable('packages_cloneproject_configurations');
    }
}
