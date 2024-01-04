<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addincheckout extends Migration
{
    public function up()
    {
        $fields = [
            'guest_fullname' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'guest_contact_no' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'guest_email' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ]
        ];
        $this->forge->addColumn('tbl_payment_checkout', $fields);
    }

    public function down()
    {
        
    }
}
