<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserTransaction extends Migration
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
				'customer_id' => [
					'type' => 'INT',
					'constraint' => 11,
				],
				'user_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'ota, provider, admin ID',
				],
				'user_type' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
					'comment' => 'save user role text',
					'null' => false,
				],
				'transaction_type' => [
					'type' => 'ENUM',
					'constraint' => ['Dr', 'Cr'],
					'default' => 'Dr',
					'null' => false,
				],
				'transaction_reason' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => false,
				],
				'currency_code' => [
					'type' => 'VARCHAR',
					'constraint' => '5',
					'default' => 'INR',
					'null' => false,
				],
				'transaction_amount' => [
					'type' => 'INT',
					'constraint' => '11',
					'null' => false,
				],
				'transaction_id' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
					'null' => false,
				],
				'transaction_status' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
					'comment' => 'success/failed/pending/cancelled',
					'null' => false,
				],
				'transaction_date' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
					'null' => false,
				],
				'payment_method' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
					'null' => false,
				],
				'service_type' => [
					'type' => 'ENUM',
					'constraint' => ['package','activities'],
					'default' => 'package',
					'null' => false,
				],
				'service_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'package/activities Ids',
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
		$this->forge->createTable('tbl_user_transactions');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_user_transactions');
	}
}
