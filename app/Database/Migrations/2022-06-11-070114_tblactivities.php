<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tblactivities extends Migration
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
				'activitie_title' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'provider_id' => [
					'type' => 'BIGINT',
					'constraint' => 20,
					'after'=> 'id',
				],
				'city_loaction' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'ideal_for' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'main_img' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'included' => [
					'type' => 'VARCHAR',
					'constraint' => '155',
				],
				'not_included' => [
					'type' => 'VARCHAR',
					'constraint' => '155',
				],
				'pickup_loaction' => [
					'type' => 'VARCHAR',
					'constraint' => '155',
				],
				'drop_loaction' => [
					'type' => 'VARCHAR',
					'constraint' => '155',
				],
				'drop_loaction' => [
					'type' => 'VARCHAR',
					'constraint' => '155',
				],
				'status' => [
					'type' => 'ENUM',
					'constraint' => ['active', 'inactive'],
					'default' => 'active',
					'null' => false,
				],
				'status_by_admin' => [
					'type' => 'ENUM',
					'constraint' => ['active','inactive'],
					'default' => 'active',
					'null' => false,
				],
				'accommodations' => [
					'type' => 'ENUM',
					'constraint' => ['yes', 'no'],
					'default' => 'yes',
					'null' => false,
				],
				'accommodations_title' => [
					'type' => 'VARCHAR',
					'constraint' => '50',
				],
				'accommodations_detail' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'return_policy' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'type_of_activitie' => [
					'type' => 'ENUM',
					'constraint' => ['b2b', 'b2c', 'both'],
					'default' => 'both',
					'null' => false,
				],
				'activitie_amount' => [
					'type' => 'TEXT',
					'constraint' => '10',
				],
				'reason' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'language' => [
					'type' => 'TEXT',
					'constraint' => '10',
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
		$this->forge->createTable('tbl_activities');
	}

	public function down()
	{
		$this->forge->dropTable('tbl_activities');
	}
}
