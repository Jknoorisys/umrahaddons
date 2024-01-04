<?php

namespace App\Models;

use CodeIgniter\Model;

class CheckoutModel extends Model
{
	protected $table = 'tbl_payment_checkout';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['session_id','customer_stripe_email','customer_stripe_id','customer_stripe_name','stripe_status','object','amount_total','customer_email','currency','payment_intent','payment_status','url','customer_details','user_id','user_role','ota_id','service_id','service_type','status','guest_fullname','guest_contact_no','guest_email'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';

	
	
}

/* End of file CheckoutModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/CheckoutModel.php */