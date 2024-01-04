<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblCuisionMaster extends Migration
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
            'name' => [ 
                'type' => 'VARCHAR',
                'constraint' => '255',
                null => true,
            ],
            'status' => [      
                'type' => 'TINYINT',
                'constraint' => '2',
                'default' => '1',
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
		$this->forge->createTable('tbl_cuision_master');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_cuision_master');
    }
}
