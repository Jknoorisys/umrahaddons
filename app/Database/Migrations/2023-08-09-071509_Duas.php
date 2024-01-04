<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Duas extends Migration
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
            'user_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'user_type' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'admin'
            ],
            'title_en' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'reference_en' => [ 
                'type' => 'LONGTEXT',
                null => true,
            ],
            'title_ur' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'reference_ur' => [ 
                'type' => 'LONGTEXT',
                null => true,
            ],
            'image' => [
				'type' => 'TEXT',
                null => true,
			],
            'type' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'umrah',
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
		$this->forge->createTable('tbl_duas');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_duas');
    }
}
