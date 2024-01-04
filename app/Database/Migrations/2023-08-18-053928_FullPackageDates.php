<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FullPackageDates extends Migration
{
    public function up()
    {
        $fields =[
			'id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'full_package_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
            'city' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'departure_date' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'arrival_date' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'days' => [
				'type' => 'BIGINT',
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
			]
		];$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_full_package_dates');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_full_package_dates');
    }
}
