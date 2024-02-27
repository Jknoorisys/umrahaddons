<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DepartureCityMaster extends Migration
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
                'constraint' => '250',
            ],

            'status' => [      
                'type' => 'TINYINT',
                'constraint' => '2',
                'default' => '1',
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
		$this->forge->createTable('tbl_departure_city_master');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_departure_city_master');
    }
}
