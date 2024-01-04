<?php
namespace App\Models;

use CodeIgniter\Model;

class CountryModel extends Model 
{
	protected $table = 'countries';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['name','shortname','phonecode'];
}

/* End of file CountryModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/CountryModel.php */