<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageModels extends Model
{
	protected $table = 'tbl_package';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['package_type', 'individual_price', 'package_title', 'package_details', 'status_by_admin', 'provider_id', 'city_loaction', 'ideal_for', 'main_img', 'included', 'not_included', 'pickup_loaction', 'drop_loaction', 'status', 'accommodations', 'accommodations_title', 'accommodations_detail', 'return_policy', 'type_of_package', 'package_amount', 'reason', 'language','package_duration', 'ziyarat_points', 'is_featured', 'created_date', 'updated_date'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';

	public function getallTransactionlist(array $trnx_filters, $per_page, $page_no, $add_filter, $abc)
	{

		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['package_title']) && $trnx_filters['package_title'] != "") {
			$criterial .= " AND l.package_title LIKE '%" . $trnx_filters['package_title'] . "%'";
		}

		if (isset($trnx_filters['city_loaction']) && $trnx_filters['city_loaction'] != "") {
			$criterial .= " AND l.city_loaction LIKE '%" . $trnx_filters['city_loaction'] . "%'";
		}
		
		if (isset($trnx_filters['pickup_loaction']) && $trnx_filters['pickup_loaction'] != "") {
			$criterial .= " AND l.pickup_loaction LIKE '%" . $trnx_filters['pickup_loaction'] . "%'";
		}

		$criterial .= " AND l.status = 'active'";
		
		$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name FROM tbl_package AS l
		  LEFT JOIN tbl_provider AS p ON p.id = l.provider_id ";

		// 	$query = "SELECT l.*,c.firstname AS country_name, FROM tbl_package AS l
		//    LEFT JOIN tbl_provider AS c ON c.id = l.provider_id ";

		$query .= "WHERE 1";
		$query .= $criterial;

		if ($abc == 0) {
			$query .= " ORDER BY l.created_date DESC";
			return $this->db->query($query)->getResult();
		} else {
			$query .= " ORDER BY l.created_date DESC LIMIT " . $page_no . "," . $per_page;
			return $this->db->query($query)->getResult();
		}
		// echo json_encode($total_record);die();
		return false;
	}

	// list of provider packages for provider 
	public function getproviderpackage(array $trnx_filters, $per_page, $page_no, $add_filter, $abc, $provider_id)
	{
		// echo json_encode($provider_id);die(); ORDER BY l.id DESC
		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['package_title']) && $trnx_filters['package_title'] != "") {
			$criterial .= " AND l.package_title LIKE '%" . $trnx_filters['package_title'] . "%'";
		}

		if (isset($trnx_filters['city_loaction']) && $trnx_filters['city_loaction'] != "") {
			$criterial .= " AND l.city_loaction LIKE '%" . $trnx_filters['city_loaction'] . "%'";
		}
		
		if (isset($trnx_filters['pickup_loaction']) && $trnx_filters['pickup_loaction'] != "") {
			$criterial .= " AND l.pickup_loaction LIKE '%" . $trnx_filters['pickup_loaction'] . "%'";
		}

		$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name FROM tbl_package AS l 
		  LEFT JOIN tbl_provider AS p ON p.id = $provider_id  WHERE l.provider_id = $provider_id AND l.status ='active' ";

		// $query .= "WHERE 1";
		$query .= $criterial;

		$query .= " ORDER BY l.id DESC";

		if ($abc == 0) {
			return $this->db->query($query)->getResult();
		} else {
			$query .= " LIMIT " . $page_no . "," . $per_page;
			return $this->db->query($query)->getResult();
		}
		// echo json_encode($total_record);die();
		return false;
	}

	// list of provider packages for user
	public function getuserpackage(array $trnx_filters, $per_page, $page_no, $add_filter, $abc, $active, $min_val)
	{

		// echo json_encode($provider_id);die();
		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		// if (isset($trnx_filters['search_word']) && $trnx_filters['search_word'] != "") {
		// 	$criterial .= " AND l.package_title  LIKE'%" . $trnx_filters['search_word'] . "%'"."OR"." l.city_loaction  LIKE'%" . $trnx_filters['search_word'] . "%'";
		// }

		if (!empty($trnx_filters['search_word'])) {
			$searchWord = $trnx_filters['search_word'];
			// Use parentheses to group the OR conditions correctly
			$criterial .= " AND (l.package_title LIKE '%" . $searchWord . "%' OR l.city_loaction LIKE '%" . $searchWord . "%')";
		}
		
		if (isset($trnx_filters['package_title']) && $trnx_filters['package_title'] != "") {
			$criterial .= " AND l.package_title = '" . $trnx_filters['package_title'] . "'";
		}

		if (isset($trnx_filters['city_loaction']) && $trnx_filters['city_loaction'] != "") {
			$criterial .= ' AND l.city_loaction  LIKE "%'.$trnx_filters['city_loaction'].'%" ';
			// $criterial .= " AND l.city_loaction  LIKE' %" . $trnx_filters['city_loaction'] . "% ' ";
		}

		if (isset($trnx_filters['package_amount']) && $trnx_filters['package_amount'] != "") {
			$criterial .= " AND l.package_amount BETWEEN " . $min_val . " AND " . $trnx_filters['package_amount'] . "";
		}

		if (isset($trnx_filters['ideal_for']) && $trnx_filters['ideal_for'] != "") {
			$criterial .= " AND l.ideal_for  LIKE'%" . $trnx_filters['ideal_for'] . "%'";
		}

		// $query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name FROM tbl_package AS l 
		//   LEFT JOIN tbl_provider AS p ON p.id = l.provider_id  WHERE l.status = 'active' AND  l.status_by_admin= 'active'";

		$query = "SELECT l.*, CONCAT(p.firstname, ' ', p.lastname) as provider_name FROM tbl_package AS l 
		LEFT JOIN tbl_provider AS p ON p.id = l.provider_id 
		WHERE l.status = 'active' AND l.status_by_admin = 'active'";

		// 	$query = "SELECT l.*,c.firstname AS country_name, FROM tbl_package AS l
		//    LEFT JOIN tbl_provider AS c ON c.id = l.provider_id ";

		// $query .= "WHERE 1";
		$query .= $criterial;

		$query .= " ORDER BY l.id DESC";

		if ($abc == 0) {
			return $this->db->query($query)->getResult();
		} else {
			$query .= " LIMIT " . $page_no . "," . $per_page;
			return $this->db->query($query)->getResult();
		}
		// echo json_encode($total_record);die();
		return false;
	}

	// Example for search in single line
	public function getUserSearchPackage(array $trnx_filters, $per_page, $page_no, $add_filter, $abc, $active)
	{
		$criteria = '';

		if (isset($trnx_filters['search_word']) && $trnx_filters['search_word'] != "") {
			$criteria .= " AND ((l.package_title LIKE '%" . $trnx_filters['search_word'] . "%' OR l.city_loaction LIKE '%" . $trnx_filters['search_word'] . "%'))";
		}

		$criteria .= " AND l.status = 'active' AND l.status_by_admin = 'active'";

		$query = $this->db->table('tbl_package l')
			->select('l.*, CONCAT(p.firstname, \' \', p.lastname) as provider_name')
			->join('tbl_provider p', 'p.id = l.provider_id', 'left')
			->where($criteria);

		if ($abc != 0) {
			$query->limit($per_page, $page_no);
		}

		return $query->get()->getResult();
	}



}

/* End of file PackageModels.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/PackageModels.php */