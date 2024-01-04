<?php
namespace App\Models;

use CodeIgniter\Model;

class StateModel extends Model 
{
	protected $table = 'states';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['name','country_id'];
}

/* End of file StateModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/StateModel.php */