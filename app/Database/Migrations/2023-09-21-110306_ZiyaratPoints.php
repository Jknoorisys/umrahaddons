<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ZiyaratPoints extends Migration
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
            'city_id' => [ 
                'type' => 'BIGINT',
                'constraint' => 20,
            ],
            'name_en' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'name_ur' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'title_en' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'title_ur' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'description_en' => [ 
                'type' => 'LONGTEXT',
                null => true,
            ],
            
            'description_ur' => [ 
                'type' => 'LONGTEXT',
                null => true,
            ],
            'main_img' => [
				'type' => 'TEXT',
                 null => true,
			],
            'video' => [
				'type' => 'TEXT',
                 null => true,
			],
            'address' => [
				'type' => 'TEXT',
                 null => true,
			],

            'lat' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'long' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
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
		$this->forge->createTable('tbl_ziyarat_points');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_ziyarat_points');
    }
}
