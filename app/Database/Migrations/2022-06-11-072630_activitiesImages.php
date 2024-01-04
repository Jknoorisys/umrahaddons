<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ActivitiesImages extends Migration
{
	public function up()
	{
		$fields = [
			'id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'activitie_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active', 'inactive'],
				'default' => 'active',
				'null' => false,
			],
			'activitie_img' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
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
		$this->forge->createTable('tbl_activitie_image');
	}

	public function down()
	{
		$this->forge->dropTable('tbl_activitie_image');
	}
}
