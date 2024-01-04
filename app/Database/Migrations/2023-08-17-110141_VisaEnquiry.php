<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class VisaEnquiry extends Migration
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
            'name' => [
				'type' => 'VARCHAR',
                'constraint' => '255',
			],
            'country_code' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
            ],
            'mobile' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
            ],
            'no_of_persons' => [ 
                'type' => 'FLOAT',
            ],
            'booking_status' => [
                'type' => 'ENUM',
					'constraint' => ['pending', 'accepted', 'rejected'],
					'default' => 'pending',
					'null' => false,
            ],
            'reject_reason' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
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
		$this->forge->createTable('tbl_visa_enquiry');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_visa_enquiry');
    }
}
