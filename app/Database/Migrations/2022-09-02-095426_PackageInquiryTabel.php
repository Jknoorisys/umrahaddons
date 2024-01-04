<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PackageInquiryTabel extends Migration
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
            'provider_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'ota_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'package_id' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'from_date' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'no_of_pax' => [ 
                'type' => 'INT',
                'constraint' => '11',
            ],
            'package_amount' => [ 
                'type' => 'VARCHAR',
                'constraint' => '50',
                null => true,
            ],
            'total_amount' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                null => true,
            ],
            'full_name' => [
				'type' => 'TEXT',
                null => true,
			],
            'email_address' => [
				'type' => 'TEXT',
                null => true,
			],
            'country' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
            ],
            'mobile' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
            ],
            'booking_status' => [ 
                'type' => 'VARCHAR',
                'constraint' => '35',
                'default' => 'pending',
            ],
            'reject_reason' => [
				'type' => 'TEXT',
                null => true,
			],
            'action_by' => [ 
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
		$this->forge->createTable('tbl_package_enquiry');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_package_enquiry');
    }
}
