<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BookingPaymentRecod extends Migration
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
				'service_type' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Package / Activities',
				],
				'sevice_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'ota, provider, admin ID',
				],
				'booking_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'ota, provider, admin ID',
				],
				'user_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'user id',
				],
				'Provider_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'provider id',
				],
				'ota_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'comment' => 'ota id',
				],
				'package_rate' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Package Amount',
				],
				'admin_commision' => [
					'type' => 'VARCHAR',
					'constraint' => '10',
					'comment' => 'admin commision',
				],
				'ota_commision' => [
					'type' => 'VARCHAR',
					'constraint' => '10',
					'comment' => 'admin commision',
				],
				'provider_commision' => [
					'type' => 'VARCHAR',
					'constraint' => '10',
					'comment' => 'admin commision',
				],
				'admin_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'admin amount ',
				],
				'ota_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'ota amount',
				],
				'provider_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'provider amount',
				],
				'date' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
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
		$this->forge->createTable('tbl_booking_payment_record');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_booking_payment_record');
	}
}
