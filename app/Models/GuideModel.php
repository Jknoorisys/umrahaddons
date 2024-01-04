<?php

namespace App\Models;

use CodeIgniter\Model;

class GuideModel extends Model
{
	protected $table = 'tbl_guide';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['firstname', 'lastname', 'contact', 'email', 'password', 'status', 'is_verify', 'reason', 'token', 'language', 'profile_pic', 'cover_pic', 'govt_id_doc', 'dob', 'nationality', 'education', 'experience', 'created_date', 'document_1', 'document_2', 'document_3', 'document_4', 'home_address', 'city', 'country', 'about_us','device_type','device_token','is_deleted'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';



	public function getguidedetail(array $trnx_filters, $per_page, $page_no, $add_filter, $abc)
	{

		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['guide_name']) && $trnx_filters['guide_name'] != "") {
			$criterial .= " AND l.firstname LIKE '%" . $trnx_filters['guide_name'] . "%'";
		}

		if (isset($trnx_filters['guide_email']) && $trnx_filters['guide_email'] != "") {
			$criterial .= " AND l.email LIKE '%" . $trnx_filters['guide_email'] . "%'";
		}

		$query = "SELECT l.* FROM tbl_guide AS l ";

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

/* End of file GuideModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/GuideModel.php */