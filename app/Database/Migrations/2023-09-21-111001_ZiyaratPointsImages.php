<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ZiyaratPointsImages extends Migration
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
			'point_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
            'image' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
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
		];
		
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_ziyarat_point_images');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_ziyarat_point_images');
    }
}
