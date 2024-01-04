<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LandingPageBanners extends Migration
{
    public function up()
    {
        $fields =[
			'id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'auto_increment' => true,
			],

			'package_id' => [
				'type' => 'BIGINT',
				'constraint' => 20,
			],

            'image' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],

            'title' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],

            'description' => [
                'type' => 'TEXT',
            ],

            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'deleted'],
                'default' => 'active',
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
		$this->forge->createTable('tbl_landing_page_banners');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_landing_page_banners');
    }
}
