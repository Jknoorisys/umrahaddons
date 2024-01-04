<?php
namespace App\Models;

use CodeIgniter\Model;

class Admin_transaction_Model extends Model 
{
	protected $table = 'tbl_admin_transactions';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['admin_id','user_id','user_type','transaction_type','service_type','service_id','transaction_reason','currency_code','account_id','old_balance','transaction_amount','current_balance','transaction_id','transaction_status','transaction_date','payment_method', 'booking_id', 'payment_session_id'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	
}

/* End of file Admin_transaction_Model.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/Admin_transaction_Model.php */