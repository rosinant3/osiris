<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
* ./vendor/bin/doctrine-migrations migrations:migrate;
 */
final class Version20190330144251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        $options = array('autoincrement' => true);
        $table = $schema->createTable("Gallery");
        $table->addColumn("id", 'integer', $options);
        $table->addColumn("title", "string", ["length" => 250]);
        $table->addColumn("description", "text");
        $table->addColumn("type", "string", ["length" => 250]);
        $table->addColumn("date", "string", ["notnull" => false]);

        $table->setPrimaryKey(["id"]);

    }

    public function down(Schema $schema) : void
    {
        
        $schema->dropTable('GalleryItems');

    }
}
