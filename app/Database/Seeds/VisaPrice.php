<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VisaPrice extends Seeder
{
    public function run()
    {
        $data = [
            'price' => '42000',
            'currency'    => '₹',
            'duration' => '2 years',
            'status'   => 1,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
      
        $this->db->table('tbl_visa')->insert($data);
    }
}
