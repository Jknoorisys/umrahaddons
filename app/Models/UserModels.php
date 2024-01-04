<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModels extends Model 
{
	protected $table = 'tbl_user';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['firstname','created_by_id','created_by_role','lastname','username','email','password','plain_password','country_code','mobile','dob','gender','profile_pic','city','state','country','zip_code','token','user_role','id_prrof','document','status','created_by','otp','device_type','device_token'];
	protected $createdField  = 'created_date';
  	protected $updatedField  = 'updated_date';

	  public function getallTransactionlist(array $trnx_filters, $per_page, $page_no, $add_filter, $abc)
	{

		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['firstname']) && $trnx_filters['firstname'] != "") {
			$criterial .= " AND l.firstname LIKE '%" . $trnx_filters['firstname'] . "%'";
		}

		if (isset($trnx_filters['lastname']) && $trnx_filters['lastname'] != "") {
			$criterial .= " AND l.lastname LIKE '%" . $trnx_filters['lastname'] . "%'";
		}

		if (isset($trnx_filters['email']) && $trnx_filters['email'] != "") {
			$criterial .= " AND l.email LIKE '%" . $trnx_filters['email'] . "%'";
		}

		if (isset($trnx_filters['mobile']) && $trnx_filters['mobile'] != "") {
			$criterial .= " AND l.mobile LIKE '%" . $trnx_filters['mobile'] . "%'";
		}

		$query = "SELECT l.*,c.name AS country_name,s.name AS state_name,ci.name AS city_name FROM tbl_user AS l
       LEFT JOIN countries AS c ON c.id = l.country LEFT JOIN states AS s ON s.id = l.state LEFT JOIN cities AS ci ON ci.id = l.city ";


		$query .= "WHERE 1";
		$query .= $criterial;

		if ($abc == 0) {
			return $this->db->query($query)->getResult();
		} else {
			$query .= " LIMIT " . $page_no . "," . $per_page;
			return $this->db->query($query)->getResult();
		}
		// echo json_encode($total_record);die();
		return false;
	}
}

/* End of file UserModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/UserModel.php */