<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AudioFiles extends Migration
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
            'title' => [ 
                'type' => 'VARCHAR',
                'constraint' => 250,
            ],
            'artist' => [ 
                'type' => 'VARCHAR',
                'constraint' => 250,
            ],
            'album' => [ 
                'type' => 'VARCHAR',
                'constraint' => 250,
                null => true,
            ],
            'image' => [ 
                'type' => 'VARCHAR',
                'constraint' => '250',
                null => true,
            ],
            'audio' => [ 
                'type' => 'TEXT',
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
		$this->forge->createTable('tbl_audio_files');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_audio_files');
    }
}
