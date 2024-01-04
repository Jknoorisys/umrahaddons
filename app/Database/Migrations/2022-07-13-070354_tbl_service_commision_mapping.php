<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblServiceCommisionMapping extends Migration
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
			'service_id' => [
				'type' => 'INT',
				'constraint' => '9',
			],
			'service_type' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => '10',
			],
			'user_role' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'commision_in_percent' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
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
		$this->forge->createTable('tbl_service_commision_mapping');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_service_commision_mapping');
	}
}
