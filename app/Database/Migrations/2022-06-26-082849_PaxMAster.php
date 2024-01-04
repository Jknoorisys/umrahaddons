<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaxMAster extends Migration
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
			'min_pax' => [
				'type' => 'BIGINT',
				'constraint' => 20,
			],
			'max_pax' => [
				'type' => 'BIGINT',
				'constraint' => 20,
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
		$this->forge->createTable('tbl_pax_master');
	}

	public function down()
	{
		$this->forge->dropTable('tbl_pax_master');
	}
}
