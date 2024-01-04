<?php
namespace App\Models;

use CodeIgniter\Model;

class ImagePackageModels extends Model 
{
	protected $table = 'tbl_package_image';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['package_id','status','package_imgs'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	
}

/* End of file ImagePackageModels.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/ImagePackageModels.php */