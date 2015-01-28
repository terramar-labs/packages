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
        $table = $schema->getTable('packages_sami_configurations');
        $table->addColumn('remote_repo_path', 'string', array('default' => ''));
        $table->addColumn('title', 'string', array('default' => ''));
        $table->addColumn('theme', 'string', array('default' => ''));
        $table->addColumn('tags', 'string', array('default' => ''));
        $table->addColumn('refs', 'string', array('default' => ''));
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('packages_sami_configurations');
        $table->dropColumn('remote_repo_path');
        $table->dropColumn('title');
        $table->dropColumn('theme');
        $table->dropColumn('tags');
        $table->dropColumn('refs');
    }
}
