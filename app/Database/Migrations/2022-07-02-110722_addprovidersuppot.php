<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addprovidersuppot extends Migration
{

	public function up()
	{
		$fields = [
			'supporter_no' => ['type' => 'VARCHAR', 'constraint' => 20, 'after' => 'status']
		];
		$this->forge->addColumn('tbl_provider', $fields);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		//
	}
}
