<?php
namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model 
{
	protected $table = 'tbl_admin_accounts';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['user_id','user_role','account_no','account_type','bank_name','bank_branch','amount','remark','status'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	
}

/* End of file AccountModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/AccountModel.php */