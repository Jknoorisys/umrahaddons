<?php

namespace App\Models;

use CodeIgniter\Model;

class MealsBookingModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'meals_booking';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['provider_id','meals_id','user_id','ota_id','full_name','mobile','no_of_person','start_date','end_date','meals_type','meals_service','city','lat','long','address','cost_per_day_person','no_of_days','total_cost','notes','status','booking_status','reject_reason','created_date','booking_status_user','booking_status_stripe','payment_status','provider_payment_status','session_id','checkout_id','admin_commision','ota_commision','provider_commision','total_admin_comm_amount','remaining_admin_comm_amount','ota_commision_amount','provider_amount','ota_payment_status','remianing_amount_pay'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
