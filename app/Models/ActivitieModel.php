<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivitieModel extends Model
{
	protected $table = 'tbl_activities';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['activitie_title','status_by_admin','provider_id', 'city_loaction','type_of_activitie', 'ideal_for', 'main_img', 'included', 'not_included', 'pickup_loaction', 'drop_loaction', 'status', 'accommodations', 'activitie_amount','accommodations_title', 'accommodations_detail', 'return_policy', 'type_of_package', 'package_amount', 'reason', 'language'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';


	// list of provider packages
	public function getproviderActivitie(array $trnx_filters, $per_page, $page_no, $add_filter, $abc,$provider_id)
	{

		// echo json_encode($provider_id);die();
		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['activitie_title']) && $trnx_filters['activitie_title'] != "") {
			$criterial .= " AND l.activitie_title LIKE '%" . $trnx_filters['activitie_title'] . "%'";
		}

		if (isset($trnx_filters['city_loaction']) && $trnx_filters['city_loaction'] != "") {
			$criterial .= " AND l.city_loaction LIKE '%" . $trnx_filters['city_loaction'] . "%'";
		}

		if (isset($trnx_filters['included']) && $trnx_filters['included'] != "") {
			$criterial .= " AND l.included LIKE '%" . $trnx_filters['included'] . "%'";
		}

		if (isset($trnx_filters['pickup_loaction']) && $trnx_filters['pickup_loaction'] != "") {
			$criterial .= " AND l.pickup_loaction LIKE '%" . $trnx_filters['pickup_loaction'] . "%'";
		}

		if (isset($trnx_filters['drop_loaction']) && $trnx_filters['drop_loaction'] != "") {
			$criterial .= " AND l.drop_loaction LIKE '%" . $trnx_filters['drop_loaction'] . "%'";
		}

		if (isset($trnx_filters['accommodations']) && $trnx_filters['accommodations'] != "") {
			$criterial .= " AND l.accommodations LIKE '%" . $trnx_filters['accommodations'] . "%'";
		}

		if (isset($trnx_filters['type_of_activitie']) && $trnx_filters['type_of_activitie'] != "") {
			$criterial .= " AND l.type_of_activitie LIKE '%" . $trnx_filters['type_of_activitie'] . "%'";
		}
		
		$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name FROM tbl_activities AS l 
		   JOIN tbl_provider AS p ON p.id = $provider_id  WHERE l.provider_id = $provider_id";

		// 	$query = "SELECT l.*,c.firstname AS country_name, FROM tbl_package AS l
		//    LEFT JOIN tbl_provider AS c ON c.id = l.provider_id ";

		// $query .= "WHERE 1";
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

/* End of file ActivitieModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/ActivitieModel.php */