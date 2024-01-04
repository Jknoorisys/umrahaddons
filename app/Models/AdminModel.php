<?php
namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model 
{
	protected $table = 'tbl_admin';
	protected $primaryKey = 'id';
	protected $useTimestamps = false;
	protected $allowedFields = ['username','email','password','mobile','token','city','state','zip_code','address','profile_pic'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';
}

/* End of file AdminModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/AdminModel.php */