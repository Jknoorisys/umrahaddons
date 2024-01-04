<?php
namespace App\Models;

use CodeIgniter\Model;

class OtaProviderAccountModel extends Model 
{
	protected $table = 'tbl_ota_provider_account';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['user_role','user_id','total_amount','pending_amount','withdrawal_amount'];
    protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';
}

/* End of file OtaProviderAccountModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/OtaProviderAccountModel.php */