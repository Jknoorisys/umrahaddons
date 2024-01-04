<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Visa extends Migration
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

            'currency' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => '₹',
            ],

            'price' => [ 
                'type' => 'FLOAT',
                'default' => '0',
            ],

            'duration' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
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
		$this->forge->createTable('tbl_visa');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_visa');
    }
}
