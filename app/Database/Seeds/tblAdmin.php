<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TblAdmin extends Seeder
{
	public function run()
	{
		$data = [
      'username' => 'admin',
      'email'    => 'admin@umrah.com',
      'password' => '$2y$10$ozZZlefg23AFLskM0d3g9.lQc3t0fMwyW78sBlDWlGyFhV12Mmzpm',
      'mobile'   => '',
      'city'   => 'Kholapoor',
      'state'   => 'Maharastra',
      'country'   => 'India',
      'zip_code'   => 423203,
      'status'   => 1,
      'created_date'   => date('Y-m-d H:i:s'),
      'updated_date'   => date('Y-m-d H:i:s'),
    ];

    $this->db->table('tbl_admin')->insert($data);
	}
}
/* End of file  Seeder TblAdmin.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Databse/Seeds/TblAdmin.php */
