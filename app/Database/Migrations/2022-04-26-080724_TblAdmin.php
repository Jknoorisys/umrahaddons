<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblAdmin extends Migration
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
			'token' => [
				'type' => 'TEXT',
				'null' => false,
			],
			'profile_pic' => [
				'type' => 'TEXT',
				'null' => false,
			],
			'mobile' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => false,
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
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['Active','Inactive'],
				'default' => 'Active',
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
			],
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_admin');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_admin');
    }
}

/* End of file TblAdmin.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Databse/Migration/TblAdmin.php */
