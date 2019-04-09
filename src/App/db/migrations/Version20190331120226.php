<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
* ./vendor/bin/doctrine-migrations migrations:migrate;
 */
final class Version20190331120226 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {

        $options = array('autoincrement' => true);
        $table = $schema->createTable("GalleryItems");
        $table->addColumn("id", 'integer', $options);
        $table->addColumn("filename", "string", ["length" => 250]);
        $table->addColumn("directory", "string", ["notnull" => false]);
        $table->addColumn("gallery_id", "integer", ["length" => 250]);
 
        $table->setPrimaryKey(["id"]);
        $table->addForeignKeyConstraint("Gallery", 
        array('gallery_id'), array('id'));

    }

    public function down(Schema $schema) : void
    {
        
        $schema->dropTable('Gallery');

    }
}
