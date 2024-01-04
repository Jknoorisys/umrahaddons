<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FullPackageInclusions extends Migration
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
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
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
		$this->forge->createTable('tbl_full_package_inclusions');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_full_package_inclusions');
    }
}
