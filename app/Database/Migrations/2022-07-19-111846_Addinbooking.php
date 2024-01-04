<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addinbooking extends Migration
{
    public function up()
    {
        $fields = [
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'checkout_id' => [
                'type' => 'INT',
                'constraint' => '9',
            ],
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
        $this->forge->addColumn('tbl_booking', $fields);
    }

    public function down()
    {
        //
    }
}
