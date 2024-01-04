<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdminAccount extends Migration
{
	public function up()
	{
		$fields = 
		[
			'id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 11,
			],
			'user_role' => [
				'type' => 'VARCHAR',
				'constraint' => 11,
			],
			'account_no' => [
				'type' => 'TEXT',
				'null' => false,
			],
			'account_type' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => false,
			],
			'bank_name' => [
				'type' => 'VARCHAR',
				'constraint' => '150',
			],
			'bank_branch' => [
				'type' => 'VARCHAR',
				'constraint' => '150',
			],
			'amount' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'remark' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['Active','Inactive'],
				'default' => 'Active',
				'null' => false,
			],
			'created_by' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
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
			],
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_admin_accounts');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_admin_accounts');
	}
}
