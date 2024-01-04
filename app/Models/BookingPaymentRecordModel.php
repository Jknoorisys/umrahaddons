<?php
namespace App\Models;

use CodeIgniter\Model;

class BookingPaymentRecordModel extends Model 
{
	protected $table = 'tbl_booking_payment_record';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['service_type','sevice_id','booking_id','user_id','Provider_id','ota_id','package_rate','admin_commision','ota_commision','provider_commision','admin_amount','ota_amount','provider_amount','date'];
    protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';
}

/* End of file BookingPaymentRecordModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/BookingPaymentRecordModel.php */