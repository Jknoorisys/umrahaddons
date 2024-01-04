<?php
namespace App\Models;

use CodeIgniter\Model;

class ServiceCommisionModel extends Model 
{
	protected $table = 'tbl_service_commision_mapping';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['service_id','service_type','user_id','user_role','commision_in_percent','status'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';
}

/* End of file ServiceCommisionModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/ServiceCommisionModel.php */