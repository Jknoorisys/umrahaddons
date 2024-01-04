<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblMeals extends Migration
{
    public function up()
    {
        $fields = 
		[
			'id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'title' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'cuisine_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'menu_url' => [
				'type' => 'TEXT',
                'null' => false,
			],
			'no_of_person' => [
				'type' => 'INT',
				'constraint' => '10',
			],
			'cost_per_meals' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => false,
			],
            'cost_per_day' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => false,
			],
			'meals_type' => [
				'type' => 'ENUM',
				'constraint' => ['tiffin','group'],
				'null' => false,
			],
            'meals_service' => [
				'type' => 'ENUM',
				'constraint' => ['pickup','deliver'],
				'null' => false,
			],
			'pickup_address' => [
				'type' => 'TEXT',
			],
			'img_url_1' => [
				'type' => 'TEXT',
			],
            'img_url_2' => [
				'type' => 'TEXT',
			],
            'img_url_3' => [
				'type' => 'TEXT',
			],
            'thumbnail_url' => [
				'type' => 'TEXT',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive','deleted'],
				'default' => 'active',
				'null' => false,
			],
			'provider_id' => [
				'type' => 'INT',
				'constraint' => '11',
			],
			'created_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			],
			'updated_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			]
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_meals');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_meals');
    }
}
