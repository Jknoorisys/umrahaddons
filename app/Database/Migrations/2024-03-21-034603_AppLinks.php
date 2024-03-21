<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AppLinks extends Migration
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
                'constraint' => 250,
            ],
            'logo' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'play_store' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'app_store' => [ 
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
		$this->forge->createTable('tbl_app_links');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_app_links');
    }
}
