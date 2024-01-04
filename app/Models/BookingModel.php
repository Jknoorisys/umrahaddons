<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
	protected $table = 'tbl_booking';
	protected $primaryKey = 'id';
	protected $useTimestamps = true;
	protected $allowedFields = ['service_type', 'booking_status_stripe','total_admin_comm_amount','remaining_admin_comm_amount','provider_commision','ota_commision_amount','ota_amount','provider_amount','ota_payment_status','provider_payment_status','booking_status_user','','user_pax','ota_id','cars', 'booking_status_stripe','booking_status_user','','rate', 'service_id', 'user_id', 'user_role', 'from_date', 'time', 'no_of_pox', 'action_by', 'action_by_id', 'provider_id', 'booked_time', 'booked_date', 'action', 'payment_status','guest_email','guest_fullname','guest_contact_no','session_id','checkout_id','ota_commision','booking_status','reject_reason'];
	protected $createdField  = 'created_date';
	protected $updatedField  = 'updated_date';

	// list of provider packages
	public function getbookingdetailforuser(array $trnx_filters, $per_page, $page_no, $add_filter, $abc, $service_type, $logged_user_id)
	{

		// echo json_encode($provider_id);die();
		$criterial = '';
		// echo json_encode($trnx_filters);die(); 
		if (isset($trnx_filters['action']) && $trnx_filters['action'] != "") {
			$criterial .= " AND l.action = '" . $trnx_filters['action'] . "'";
		}

		if (isset($trnx_filters['rate']) && $trnx_filters['rate'] != "") {
			$criterial .= " AND l.rate = '" . $trnx_filters['rate'] . "'";
		}

		if (isset($trnx_filters['booked_date']) && $trnx_filters['booked_date'] != "") {
			$criterial .= " AND l.booked_date = '" . $trnx_filters['booked_date'] . "'";
		}
		
		if (isset($trnx_filters['payment_status']) && $trnx_filters['payment_status'] != "") {
			$criterial .= " AND l.payment_status = '" . $trnx_filters['payment_status'] . "'";
		}

		$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name,CONCAT (c.firstname,' ',c.lastname) as user_name, pa.package_title as package_name ,pax.name as pax_name ,vec.name as vec_name FROM tbl_booking AS l 
			   JOIN tbl_provider AS p ON p.id = l.provider_id JOIN tbl_user AS c ON c.id = l.user_id JOIN tbl_pax_master AS pax ON pax.id = l.no_of_pox  JOIN tbl_vehicle_master AS vec ON vec.id = l.cars  JOIN tbl_package AS pa ON pa.id = l.service_id  WHERE l.service_type = 'package' AND  l.provider_id = $logged_user_id";

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

	// list of provider packages
	public function getbookinghistoryforuser(array $trnx_filters, $per_page, $page_no, $add_filter, $abc, $service_type, $logged_user_id,$package_title)
	{

		$criterial = '';
		// if (isset($trnx_filters['package_title']) && $trnx_filters['package_title'] != "") {
		// 	$criterial .= " AND l.package_title LIKE '%" . $trnx_filters['package_title'] . "%'";
		// }

		if (isset($trnx_filters['action']) && $trnx_filters['action'] != "") {
			$criterial .= " AND l.action = '" . $trnx_filters['action'] . "'";
		}

		if($add_filter == 0)
		{
			$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name, pa.package_title as package_name ,pax.name as pax_name ,vec.name as vec_name FROM tbl_booking AS l 
			JOIN tbl_provider AS p ON p.id = l.provider_id  JOIN tbl_pax_master AS pax ON pax.id = l.no_of_pox  JOIN tbl_vehicle_master AS vec ON vec.id = l.cars  JOIN tbl_package AS pa ON pa.id = l.service_id  WHERE l.service_type = 'package'   AND  l.user_id = $logged_user_id";
		}else{
			$query = "SELECT l.*,CONCAT (p.firstname,' ',p.lastname) as provider_name, pa.package_title as package_name ,pax.name as pax_name ,vec.name as vec_name FROM tbl_booking AS l 
			JOIN tbl_provider AS p ON p.id = l.provider_id  JOIN tbl_pax_master AS pax ON pax.id = l.no_of_pox  JOIN tbl_vehicle_master AS vec ON vec.id = l.cars  JOIN tbl_package AS pa ON pa.id = l.service_id  WHERE l.service_type = 'package'   AND  l.user_id = $logged_user_id AND pa.package_title LIKE '%$package_title%'";
		}
		// $query .= "WHERE 1";
		$query .= $criterial;

		$query .= " ORDER BY l.id DESC";
		// echo json_encode($query);
		// die();
		if ($abc == 0) {

			return $this->db->query($query)->getResult();
		} else {
			$query .= " LIMIT " . $page_no . "," . $per_page;
			return $this->db->query($query)->getResult();
		}

		return false;
	}
}

/* End of file BookingModel.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Models/BookingModel.php */