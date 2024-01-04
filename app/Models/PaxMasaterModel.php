<?php
namespace App\Models;

use CodeIgniter\Model;

class PaxMasaterModel extends Model 
{
	protected $table = 'tbl_pax_master';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['name','name','status',];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

}

/* End of file PaxMasaterModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/PaxMasaterModel.php */