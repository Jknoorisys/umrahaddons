<?php
namespace App\Models;
use CodeIgniter\Model;

class CodeModel extends Model 
{
	protected $table 					= 'platform_code_counters';
	protected $primaryKey 		= 'id';
	protected $returnType     = 'array';

  protected $allowedFields = ['type','prefix','next_value','code_min_length'];
}