<?php

namespace App\Models;

use CodeIgniter\Model;

class ProviderModel extends Model
{
	protected $table = 'tbl_provider';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['bank_account','supporter_no', 'ipsc', 'plain_password', 'created_by', 'status', 'document', 'commision_percent', 'user_role', 'country', 'gender', 'company_name', 'lastname', 'firstname', 'username', 'email', 'password', 'mobile', 'token', 'city', 'state', 'zip_code', 'address', 'profile_pic','bank_name','branch_name','device_type','device_token'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';

	public function getallTransactionlist(array $trnx_filters, $per_page, $page_no, $add_filter, $abc)
	{

		$criterial = '';
		if (isset($trnx_filters['firstname']) && $trnx_filters['firstname'] != "") {
			$criterial .= " AND l.firstname LIKE '%" . $trnx_filters['firstname'] . "%'";
		}

		if (isset($trnx_filters['lastname']) && $trnx_filters['lastname'] != "") {
			$criterial .= " AND l.lastname LIKE '%" . $trnx_filters['lastname'] . "%'";
		}
		
		if (isset($trnx_filters['email']) && $trnx_filters['email'] != "") {
			$criterial .= " AND l.email LIKE '%" . $trnx_filters['email'] . "%'";
		}

		$criterial .= " AND l.status != 'deleted'";

		$query = "SELECT l.*,c.name AS country_name,s.name AS state_name,ci.name AS city_name FROM tbl_provider AS l
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

/* End of file ProviderModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/ProviderModel.php */