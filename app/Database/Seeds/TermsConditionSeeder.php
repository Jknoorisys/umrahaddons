<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TermsConditionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'details' => 'Umrah Plus Terms and Conditions',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Using Query Builder
        $this->db->table('tbl_terms_and_conditions')->insertBatch($data);
    }
}
