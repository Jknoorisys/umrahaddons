<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblEnquiry extends Migration
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
            'provider_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'meals_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'user_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'ota_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'start_date' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'end_date' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'meals_type' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'meals_service' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'no_of_person' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'notes' => [
				'type' => 'TEXT',
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
			]
		];
		$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_meals_enquiry');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_meals_enquiry');
    }
}
