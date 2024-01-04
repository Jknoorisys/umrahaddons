<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblServiceMaster extends Migration
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
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active', 'inactive'],
				'default' => 'active',
				'null' => false,
			],
			
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_service_master');
	}

	public function down()
	{
		$this->forge->dropTable('tbl_service_master');
	}
}
