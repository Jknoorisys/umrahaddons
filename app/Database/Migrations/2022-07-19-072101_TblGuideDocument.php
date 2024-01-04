<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblGuideDocument extends Migration
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
			'guide_id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
			],
			'status' => [
				'type' => 'ENUM',
				'constraint' => ['active','inactive'],
				'default' => 'active',
				'null' => false,
			],
			'guide_doc' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'created_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			],
			'updated_date' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				null => true,
			]
		];$this->forge->addField($fields);
		$this->forge->addKey('id', true);
		$this->forge->createTable('tbl_guide_doc');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_guide_doc');
    }
}
