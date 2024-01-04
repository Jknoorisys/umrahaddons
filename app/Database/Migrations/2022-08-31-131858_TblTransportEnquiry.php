<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblTransportEnquiry extends Migration
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
            'ota_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'vehicle_type' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'from_city' => [ 
                'type' => 'VARCHAR',
                'constraint' => '255',
                null => true,
            ],
            'to_city' => [ 
                'type' => 'VARCHAR',
                'constraint' => '255',
                null => true,
            ],
            'date' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'time' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
            ],
            'name' => [
				'type' => 'TEXT',
                null => true,
			],
            'mobile' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
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
		$this->forge->createTable('tbl_transport_enquiry');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_transport_enquiry');
    }
}
