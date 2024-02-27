<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TermsConditions extends Migration
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

            'details' => [
                'type' => 'LONGTEXT',
                'null' => true,
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
		$this->forge->createTable('tbl_terms_and_conditions');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_terms_and_conditions');
    }
}
