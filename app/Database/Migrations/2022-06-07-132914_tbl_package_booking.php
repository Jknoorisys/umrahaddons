<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblPackageBooking extends Migration
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
					'type' => 'ENUM',
					'constraint' => ['package', 'activitie'],
					'default' => 'package',
					'null' => false,
				],
				'service_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'user_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'user_role' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
				],
				'from_date' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'time' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'no_of_pox' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'user_pax' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'action_by' => [
					'type' => 'ENUM',
					'constraint' => ['admin', 'provider'],
					'default' => 'provider',
					'null' => false,
				],
				'action_by_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'action' => [
					'type' => 'ENUM',
					'constraint' => ['pending', 'confirm', 'completed', 'rejected'],
					'default' => 'pending',
					'null' => false,
				],
				'cars' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'after'=> 'no_of_pox',
				],
				'rate' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'after'=> 'time',
				],
				'provider_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'ota_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
				],
				'booked_time' => [
					'type' => 'VARCHAR',
					'constraint' => '200',
				],
				'booked_date' => [
					'type' => 'VARCHAR',
					'constraint' => '200',
				],
				'booking_status_user' => [
					'type' => 'ENUM',
					'constraint' => ['in-progress', 'confirm', 'failed', 'cancel'],
					'default' => 'in-progress',
					'null' => false,
				],
				'booking_status_stripe' => [
					'type' => 'ENUM',
					'constraint' => ['open', 'complete', 'failed', 'cancel'],
					'default' => 'open',
					'null' => false,
				],
				'payment_status' => [
					'type' => 'ENUM',
					'constraint' => ['pending', 'confirm', 'completed', 'rejected'],
					'default' => 'pending',
					'null' => false,
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
				'total_admin_comm_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'total amount with admin and ota commision',
				],
				'remaining_admin_comm_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'Remaining admin commision amount ',
				],
				'ota_commision_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'ota amount',
				],
				'provider_amount' => [
					'type' => 'VARCHAR',
					'constraint' => 20,
					'comment' => 'provider amount',
				],
				'ota_payment_status' => [
					'type' => 'ENUM',
					'constraint' => ['pending', 'paid', 'completed', 'rejected'],
					'default' => 'pending',
					'null' => false,
				],
				'provider_payment_status' => [
					'type' => 'ENUM',
					'constraint' => ['pending', 'paid', 'completed', 'rejected'],
					'default' => 'pending',
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
		$this->forge->createTable('tbl_booking');
	}


	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_booking');
	}
}
