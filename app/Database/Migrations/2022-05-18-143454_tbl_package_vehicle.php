<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblPackageVehicle extends Migration
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
			'package_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'no_of_pox_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'vehicle_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
			'rate' => [
				'type' => 'TEXT',
				'constraint' => '10',
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
		$this->forge->createTable('tbl_package_vehicle');
	}

	public function down()
	{
		$this->forge->dropTable('tbl_package_vehicle');
	}
}
