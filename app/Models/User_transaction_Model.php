<?php
namespace App\Models;

use CodeIgniter\Model;

class User_transaction_Model extends Model 
{
	protected $table = 'tbl_user_transactions';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['customer_id','user_id','user_type','transaction_type','transaction_reason','currency_code','transaction_amount','transaction_id','transaction_status','transaction_date','payment_method','service_type','service_id'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';
}

/* End of file User_transaction_Model.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/User_transaction_Model.php */