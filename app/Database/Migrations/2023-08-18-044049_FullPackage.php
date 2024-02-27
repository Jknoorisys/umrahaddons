<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FullPackage extends Migration
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
			'provider_id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'duration' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
			],
			'mecca_hotel' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'mecca_hotel_distance' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'madinah_hotel' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'madinah_hotel_distance' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'details' => [
				'type' => 'TEXT',
			],
			'inclusions' => [
				'type' => 'TEXT',
			],
            'main_img' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'single_rate_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'single_rate_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'double_rate_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'double_rate_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'triple_rate_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'triple_rate_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'quad_rate_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'quad_rate_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'pent_rate_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'pent_rate_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'infant_rate_with_bed_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'infant_rate_with_bed_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'infant_rate_without_bed_SAR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
            'infant_rate_without_bed_INR' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['1','0','2'],
				'default' => '1',
				'null' => false,
			],
			'created_at' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			],
			'updated_at' => [
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
