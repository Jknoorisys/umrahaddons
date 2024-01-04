<?php
namespace App\Models;

use CodeIgniter\Model;

class VehicleMasterModel extends Model 
{
	protected $table = 'tbl_vehicle_master';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['name','name','status',];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

}

/* End of file VehicleMasterModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/VehicleMasterModel.php */