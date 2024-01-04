<?php
namespace App\Models;

use CodeIgniter\Model;

class VehicleModels extends Model 
{
	protected $table = 'tbl_package_vehicle';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['package_id','vehicle_id','no_of_pox_id','rate','rate_INR'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

}

/* End of file VehicleModels.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/VehicleModels.php */