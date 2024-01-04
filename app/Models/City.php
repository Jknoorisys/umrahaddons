<?php
namespace App\Models;

use CodeIgniter\Model;

class City extends Model 
{
	protected $table = 'cities';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['name','state_id'];
}

/* End of file City.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/City.php */