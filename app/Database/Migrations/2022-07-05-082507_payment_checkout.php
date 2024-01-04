<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaymentCheckout extends Migration
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
			'session_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'object' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'amount_total' => [
				'type' => 'VARCHAR',
				'constraint' => '10',
			],
			'customer_stripe_email' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'customer_stripe_id' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
			'customer_stripe_name' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
			'currency' => [
				'type' => 'VARCHAR',
				'constraint' => '9',
			],
			'payment_intent' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'payment_status' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'stripe_status' => [
				'type' => 'VARCHAR',
				'constraint' => '30',
			],
			'url' => [
				'type' => 'TEXT',
			],
			'customer_details' => [
				'type' => 'TEXT',
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => '20',
			],
			'user_role' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'ota_id' => [
				'type' => 'INT',
				'constraint' => '20',
			],
			'service_id' => [
				'type' => 'INT',
				'constraint' => '20',
			],
			'service_type' => [
				'type' => 'VARCHAR',
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
		$this->forge->createTable('tbl_payment_checkout');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_payment_checkout');
	}
}
