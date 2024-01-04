<?php
namespace App\Models;

use CodeIgniter\Model;

class ActivitieImgModel extends Model 
{
	protected $table = 'tbl_activitie_image';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['activitie_id','status','activitie_img'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	
}

/* End of file ActivitieImgModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/ActivitieImgModel.php */