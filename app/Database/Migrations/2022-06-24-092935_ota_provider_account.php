<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OtaProviderAccount extends Migration
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
				'user_role' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Package / Activities',
				],
				'user_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'ota, provider ID',
				],
				'total_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Total Amount Till Now',
				],
				'pending_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Pending Amount ',
				],
				'withdrawal_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Withdrawal Amount Till Now',
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
		$this->forge->createTable('tbl_ota_provider_account');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_ota_provider_account');
	}
}
