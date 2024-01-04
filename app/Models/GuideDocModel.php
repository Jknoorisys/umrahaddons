<?php
namespace App\Models;

use CodeIgniter\Model;

class GuideDocModel extends Model 
{
	protected $table = 'tbl_guide_doc';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['guide_id','status','guide_doc'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	
}

/* End of file GuideDocModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/GuideDocModel.php */