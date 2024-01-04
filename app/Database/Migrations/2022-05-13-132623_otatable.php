<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Otatable extends Migration
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
			'company_name' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'domain_name' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'domain_type' => [
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
			'mobile' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => false,
			],
			'bank_account' => [
				'type' => 'VARCHAR',
				'constraint' => '30',
			],
			'dob' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'ipsc' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
			'gender' => [
				'type' => 'ENUM',
				'constraint' => ['Male','Female'],
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
			'commision_percent' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],	
			'document' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
			'supporter_no' => [
				'type' => 'VARCHAR',
				'constraint' => '20'
			],
			'supporter_email' => [
				'type' => 'VARCHAR',
				'constraint' => '20'
			],
			'website_link' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
			'facebook_link' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
			'created_by' => [
				'type' => 'Text',
				'constraint' => '11',
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
		$this->forge->createTable('tbl_ota');
	}


	//--------------------------------------------------------------------

	public function down()
	{
        $this->forge->dropTable('tbl_ota');
		
	}
}
