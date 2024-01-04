<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Adduser extends Migration
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
				'firstname' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'lastname' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'username' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'email' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => false,
				],
				'password' => [
					'type' => 'TEXT',
					'null' => false,
				],
				'plain_password' => [
					'type' => 'TEXT',
				],
				'created_by_id' => [
					'type' => 'INT',
					'constraint' => '10',
				],
				'created_by_role' => [
					'type' => 'TEXT',
					'constraint' => '10',
				],
				'country_code' => [
					'type' => 'VARCHAR',
					'constraint' => '50',
					'null' => false,
				],
				'mobile' => [
					'type' => 'VARCHAR',
					'constraint' => '50',
					'null' => false,
				],
				'dob' => [
					'type' => 'VARCHAR',
					'constraint' => '20',
				],
				'gender' => [
					'type' => 'ENUM',
					'constraint' => ['Male', 'Female'],
					'default' => 'Male',
					'null' => false,
				],
				'profile_pic' => [
					'type' => 'VARCHAR',
					'constraint' => '150',
				],
				'city' => [
					'type' => 'VARCHAR',
					'constraint' => '150',
					'comment' => 'User living city',
				],
				'state' => [
					'type' => 'VARCHAR',
					'constraint' => '150',
					'comment' => 'User living state',
				],
				'country' => [
					'type' => 'VARCHAR',
					'constraint' => '150',
					'comment' => 'User living country',
				],
				'zip_code' => [
					'type' => 'VARCHAR',
					'constraint' => '50',
				],
				'token' => [
					'type' => 'TEXT',
					'null' => false,
				],
				'user_role' => [
					'type' => 'VARCHAR',
					'constraint' => '55',
				],
				'id_prrof' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'document' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
				],
				'status' => [
					'type' => 'ENUM',
					'constraint' => ['active', 'inactive'],
					'default' => 'active',
					'null' => false,
				],
				'created_by' => [
					'type' => 'Text',
					'constraint' => '11',
				],
				'otp' => [
					'type' => 'Text',
					'constraint' => '11',
				],
				'device_type' => [
					'type' => 'Text',
					'constraint' => '11',
				],
				'device_token' => [
					'type' => 'VARCHAR',
					'constraint' => '255',
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
		$this->forge->createTable('tbl_user');
	}


	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_user');
	}
}
