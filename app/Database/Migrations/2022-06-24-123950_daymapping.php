<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Daymapping extends Migration
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
				'package_id' => [
					'type' => 'INT',
					'constraint' => 20,
					'comment' => 'Package ID',
				],
				'movement_id' => [
					'type' => 'INT',
					'constraint' => 20,
					'comment' => 'Movement Table ID',
				],
				'time' => [
					'type' => 'VARCHAR',
					'constraint' => '50',
				],
				'description' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'day' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Number Of Days',
				],
				'status' => [
					'type' => 'ENUM',
					'constraint' => ['active', 'inactive'],
					'default' => 'active',
					'null' => false,
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
		$this->forge->createTable('tbl_package_day_mapping');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_package_day_mapping');
	}
}
