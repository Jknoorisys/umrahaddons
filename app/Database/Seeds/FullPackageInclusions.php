<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FullPackageInclusions extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'    => 'Flight Ticket',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Meal',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ], 
            
            [
                'name'    => 'Ziyarat',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Laundry',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Transport',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'ZamZam',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Ahram',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Bags',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],

            [
                'name'    => 'Books',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]
        ];
      
        $this->db->table('tbl_full_package_inclusions')->insertBatch($data);
    }
}
