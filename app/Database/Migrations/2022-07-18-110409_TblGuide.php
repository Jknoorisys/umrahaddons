<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblGuide extends Migration
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
				'constraint' => '50',
			],
            'lastname' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
            'contact' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
            'email' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
			],
            'password' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
            'is_verify' => [
				'type' => 'ENUM',
				'constraint' => ['yes','no'],
				'default' => 'yes',
				'null' => false,
			],
            'reason' => [
				'type' => 'TEXT',
                'comment' => 'if guide will reject then admin give a reason',
			],
            'token' => [
				'type' => 'TEXT',
				'null' => false,
			],
            'language' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
                'comment' => 'language known by guide',
			],
            'profile_pic' => [
				'type' => 'TEXT',
				'null' => false,
			],
            'cover_pic' => [
				'type' => 'TEXT',
				'null' => false,
			],
            'govt_id_doc' => [
				'type' => 'TEXT',
				'null' => false,
			],
            'dob' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
            'nationality' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
			],
            'education' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'experience' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'home_address' => [
				'type' => 'TEXT',
                'comment'=>'address of guide'
			],
            'city' => [
				'type' => 'VARCHAR',
                'constraint' => '30',
                'comment'=>'address city of guide'
			],
            'country' => [
				'type' => 'VARCHAR',
                'constraint' => '30',
                'comment'=>'address country of guide'
			],
            'about_us' => [
				'type' => 'TEXT',
                'comment'=>'description about gudie'
			],
			'updated_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
            ],
            'created_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			]
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_guide');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('tbl_guide');
	}
}
