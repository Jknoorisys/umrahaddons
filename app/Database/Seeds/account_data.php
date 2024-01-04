<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccountData extends Seeder
{
	public function run()
	{
		$data = [
      'user_id' => '1',
      'user_role'    => 'admin',
      'account_no' => '12345678',
      'account_type'   => 'umrah',
      'bank_name'   => 'ICICI',
      'bank_branch'   => 'Malegaon',
      'amount'   => '00',
      'remark'   => 'Umrah Plus Money',
      'status'   => 'Active',
      'created_date'   => date('Y-m-d H:i:s'),
      'updated_date'   => date('Y-m-d H:i:s'),
    ];

    $this->db->table('tbl_admin_accounts')->insert($data);
	}
}
