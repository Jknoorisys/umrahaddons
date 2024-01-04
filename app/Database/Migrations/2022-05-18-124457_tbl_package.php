<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblPackage extends Migration
{
	public function up()
	{
		$fields=
		[
			'id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'package_type' => [
				'type' => 'ENUM',
				'constraint' => ['individual','group'],
				'default' => 'group',
				'null' => false,
			],
			'individual_price' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'package_title' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'package_details' => [
				'type' => 'TEXT',
				'after' => 'package_title',
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
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
			'status_by_admin' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
			'provider_id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'after'=> 'id',
			],
			'accommodations' => [
				'type' => 'ENUM',
				'constraint' => ['yes','no'],
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
			'type_of_package'=>[
				'type' => 'ENUM',
				'constraint' => ['b2b','b2c','both'],
				'default' => 'both',
				'null' => false,
			],
			'package_amount' => [
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
			'ziyarat_points' => [
				'type' => 'TEXT',
			],
			'is_featured' => [
				'type' => 'ENUM',
				'constraint' => ['yes','no'],
				'default' => 'no',
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
		$this->forge->createTable('tbl_full_package');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_full_package');
    }
}
