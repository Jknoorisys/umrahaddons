<?php
namespace App\Models;

use CodeIgniter\Model;

class DayMappingModel extends Model 
{
	protected $table = 'tbl_package_day_mapping';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['package_id','day','status','package_id','movement_id','time','description'];
    protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';
}

/* End of file DayMappingModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/DayMappingModel.php */