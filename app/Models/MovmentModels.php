<?php
namespace App\Models;

use CodeIgniter\Model;

class MovmentModels extends Model 
{
	protected $table = 'tbl_package_movment';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['package_id','day','status','language'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

}

/* End of file MovmentModels.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/MovmentModels.php */