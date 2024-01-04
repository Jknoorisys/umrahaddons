<?php

namespace App\Controllers;

use App\Models\OtaMoodel;
use App\Models\ProviderModel;
use App\Models\UserModels;
use App\Models\ActivitieImgModel;
use App\Models\ActivitieModel;
use App\Models\AdminModel;
use App\Models\PackageModels;
use App\Models\BookingModel;
use App\Models\GuideModel;
use App\Models\GuideDocModel;
use App\Libraries\MailSender;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;
use Exception;

use Config\Services;


// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ListFilter extends ResourceController
{

	private $service;
	
	public function __construct()
	{
		$this->service  = new Services();
		helper('auth');
		// $UsersModel = new UsersModel();
		$lang = $_POST["language"];
		if (!empty($lang)) {
			$language = \Config\Services::language();
			$language->setLocale($lang);
		} else {
			echo json_encode(['status' => 403, 'messages' => 'language required']);
			die();
		}

		// checkEmptyPost($_POST);

		$db = \Config\Database::connect();
		// Check Authentication
		$token = $_POST['authorization'];
		$user_id = $_POST['logged_user_id'];
		$user_role = $_POST['logged_user_role'];

		// if (!$this->service->getAccessForSignedUser($token, $user_role)) {
		// 	echo json_encode(['status' => 'failed', 'messages' => 'Access denied']);
		// 	die();
		// }
	}

	public function getKey()
	{
		return "my_application_secret";
	}

	// Check Authintication
	public function checkAuthentication($token = '', $userid = '', $role = '')
	{

		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$UserModels = new UserModels();


		$key = $this->getKey();
		try {
			$decoded = JWT::decode($token, $key, array("HS256"));
			if ($decoded) {
				$id = $decoded->id;
				if ($role == "admin") {
					$userdata = $AdminModel->where("token", $token)->where("id", $userid)->first();
				} elseif ($role == 'provider') {
					$userdata = $ProviderModel->where("token", $token)->where("id", $userid)->first();
				} elseif ($role == "user") {
					$userdata = $UserModels->where("token", $token)->where("id", $userid)->first();
				}
				// elseif ($role == 5) {
				// 	$userdata = $customer->where("token", $token)->where("id", $userid)->first();
				// } else {
				// 	$userdata = $Employee->where("token", $token)->where("id", $userid)->first();
				// }
				if (!empty($userdata)) {
					return true;
				} else {
					return false;
				}
			}
		} catch (Exception $ex) {
			return false;
		}
		return $token;
	}

	// List of  provider with filter
	public function providerlist()
	{
		$ProviderModel = new ProviderModel();
		$logged_user_role = $this->request->getPost('logged_user_role');
		$logged_user_id = $this->request->getPost('logged_user_id');
		// $user_role = $this->request->getPost('user_role');
		$add_filter = $this->request->getPost('add_filter');

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;

		$filter['transfer']['firstname'] = '';
		$filter['transfer']['lastname'] = '';
		$filter['transfer']['email'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['firstname'] = (isset($_POST['firstname']) && !empty($_POST['firstname'])) ? trim($_POST['firstname']) : '';
			$filter['transfer']['lastname'] = (isset($_POST['lastname']) && !empty($_POST['lastname'])) ? trim($_POST['lastname']) : '';
			$filter['transfer']['email'] = (isset($_POST['email']) && !empty($_POST['email'])) ? trim($_POST['email']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['firstname'] = '';
			$filter['transfer']['lastname'] = '';
			$filter['transfer']['email'] = '';
		}
		$complaints = $ProviderModel->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1);
		$countlist = $ProviderModel->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0);
		if ($_POST['add_filter'] == 0) {
			$db = \Config\Database::connect();

			$builder1 = $db->table('tbl_provider as l');
			$total_loan_record = $builder1->get()->getResult();
			// $total_record = count($total_loan_record);
			$total_record = count($countlist);
		} else {
			$total_record = count($countlist);
		}
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	// List of OTA with filter
	public function otalist()
	{
		$OtaMoodel = new OtaMoodel();
		$logged_user_role = $this->request->getPost('logged_user_role');
		$logged_user_id = $this->request->getPost('logged_user_id');
		// $user_role = $this->request->getPost('user_role');
		$add_filter = $this->request->getPost('add_filter');

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;

		$filter['transfer']['firstname'] = '';
		$filter['transfer']['lastname'] = '';
		$filter['transfer']['email'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['firstname'] = (isset($_POST['firstname']) && !empty($_POST['firstname'])) ? trim($_POST['firstname']) : '';
			$filter['transfer']['lastname'] = (isset($_POST['lastname']) && !empty($_POST['lastname'])) ? trim($_POST['lastname']) : '';
			$filter['transfer']['email'] = (isset($_POST['email']) && !empty($_POST['email'])) ? trim($_POST['email']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['firstname'] = '';
			$filter['transfer']['lastname'] = '';
			$filter['transfer']['email'] = '';
		}
		$complaints = $OtaMoodel->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1);
		$countlist = $OtaMoodel->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0);
		if ($_POST['add_filter'] == 0) {
			$db = \Config\Database::connect();

			$builder1 = $db->table('tbl_ota as l');
			$total_loan_record = $builder1->get()->getResult();
			$total_record = count($total_loan_record);
			// echo json_encode($total_record);die();
		} else {
			$total_record = count($countlist);
		}
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	// List of Customer/users
	public function userlist()
	{
		$UserModels = new UserModels();
		$logged_user_role = $this->request->getPost('logged_user_role');
		$logged_user_id = $this->request->getPost('logged_user_id');
		$add_filter = $this->request->getPost('add_filter');

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;

		$filter['transfer']['firstname'] = '';
		$filter['transfer']['lastname'] = '';
		$filter['transfer']['email'] = '';
		$filter['transfer']['mobile'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['firstname'] = (isset($_POST['firstname']) && !empty($_POST['firstname'])) ? trim($_POST['firstname']) : '';
			$filter['transfer']['lastname'] = (isset($_POST['lastname']) && !empty($_POST['lastname'])) ? trim($_POST['lastname']) : '';
			$filter['transfer']['email'] = (isset($_POST['email']) && !empty($_POST['email'])) ? trim($_POST['email']) : '';
			$filter['transfer']['mobile'] = (isset($_POST['mobile']) && !empty($_POST['mobile'])) ? trim($_POST['mobile']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['firstname'] = '';
			$filter['transfer']['lastname'] = '';
			$filter['transfer']['email'] = '';
			$filter['transfer']['mobile'] = '';
		}
		$complaints = $UserModels->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1);
		$countlist = $UserModels->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0);
		// if ($_POST['add_filter'] == 0) {
		// 	$db = \Config\Database::connect();

		// 	$builder1 = $db->table('tbl_user as l');
		// 	$total_loan_record = $builder1->get()->getResult();
		// 	$total_record = count($total_loan_record);
		// 	// echo json_encode($total_record);die();
		// } else {
		// 	$total_record = count($countlist);
		// }

		$total_record = count($countlist);
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	// List of Package
	public function packagelist()
	{
		$PackageModels = new PackageModels();
		$logged_user_role = $this->request->getPost('logged_user_role');
		$logged_user_id = $this->request->getPost('logged_user_id');
		$add_filter = $this->request->getPost('add_filter');

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;

		$filter['transfer']['package_title'] = '';
		$filter['transfer']['city_loaction'] = '';
		$filter['transfer']['pickup_loaction'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['package_title'] = (isset($_POST['package_title']) && !empty($_POST['package_title'])) ? trim($_POST['package_title']) : '';
			$filter['transfer']['city_loaction'] = (isset($_POST['city_loaction']) && !empty($_POST['city_loaction'])) ? trim($_POST['city_loaction']) : '';
			$filter['transfer']['pickup_loaction'] = (isset($_POST['pickup_loaction']) && !empty($_POST['pickup_loaction'])) ? trim($_POST['pickup_loaction']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['package_title'] = '';
			$filter['transfer']['city_loaction'] = '';
			$filter['transfer']['pickup_loaction'] = '';
		}
		$complaints = $PackageModels->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1);
		$countlist = $PackageModels->getallTransactionlist($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0);
		// if ($_POST['add_filter'] == 0) {
		// 	$db = \Config\Database::connect();

		// 	$builder1 = $db->table('tbl_package as l');
		// 	$total_loan_record = $builder1->get()->getResult();
		// 	$total_record = count($total_loan_record);
		// 	// echo json_encode($total_record);die();
		// } else {
		// 	$total_record = count($countlist);
		// }

		$total_record = count($countlist);
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	//package  list by provider
	public function packageListForProvider()
	{
		$ProviderModel = new ProviderModel();
		$PackageModels = new PackageModels();
		$user_role = $this->request->getPost('logged_user_role');
		$provider_id = $this->request->getPost('logged_user_id');
		// $user_role = $this->request->getPost('user_role');
		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;		
		$add_filter = $this->request->getPost('add_filter');

		// Email Validation
		$userdata = $ProviderModel->where('user_role', $user_role)->where("id", $provider_id)->where("status", 'active')->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'status' => 404, 'messages' => lang('Language.Provider Not Found')]);
			die();
		}


		$filter['transfer']['package_title'] = '';
		$filter['transfer']['city_loaction'] = '';
		$filter['transfer']['pickup_loaction'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['package_title'] = (isset($_POST['package_title']) && !empty($_POST['package_title'])) ? trim($_POST['package_title']) : '';
			$filter['transfer']['city_loaction'] = (isset($_POST['city_loaction']) && !empty($_POST['city_loaction'])) ? trim($_POST['city_loaction']) : '';
			$filter['transfer']['pickup_loaction'] = (isset($_POST['pickup_loaction']) && !empty($_POST['pickup_loaction'])) ? trim($_POST['pickup_loaction']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['package_title'] = '';
			$filter['transfer']['city_loaction'] = '';
			$filter['transfer']['pickup_loaction'] = '';
		}
		$complaints = $PackageModels->getproviderpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $provider_id);

		$countlist = $PackageModels->getproviderpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $provider_id);
		
		if ($_POST['add_filter'] == 0) {
			$db = \Config\Database::connect();
			$builder1 = $db->table('tbl_package as l');
			$builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
			$builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
			$builder1 = $builder1->where('l.provider_id', $provider_id)->orderBy('l.id', 'DESC');
			$total_loan_record = $builder1->get()->getResult();
			$total_record = count($total_loan_record);
			// echo json_encode($total_loan_record);die();
		} else {
			$total_record = count($countlist);
		}
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	// List of booking package by provider 
	public function listOfBookingPackageByProvider()
	{
		$ProviderModel = new ProviderModel();
		$PackageModels = new PackageModels();
		$BookingModel = new BookingModel();
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$add_filter = $this->request->getPost('add_filter');
		$rate = $this->request->getPost('rate');

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;	

		$service_type = "package";


		if ($logged_user_role == "provider") {
			$filter['transfer']['action'] = '';
			$filter['transfer']['rate'] = '';
			$filter['transfer']['booked_date'] = '';
			$filter['transfer']['payment_status'] = '';
			$filter['transfer']['add_filter'] = '';
			if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
				$filter['transfer']['action'] = (isset($_POST['action']) && !empty($_POST['action'])) ? trim($_POST['action']) : '';
				$filter['transfer']['booked_date'] = (isset($_POST['booked_date']) && !empty($_POST['booked_date'])) ? trim($_POST['booked_date']) : '';
				$filter['transfer']['payment_status'] = (isset($_POST['payment_status']) && !empty($_POST['payment_status'])) ? trim($_POST['payment_status']) : '';
				$filter['transfer']['rate'] = (isset($_POST['rate']) && !empty($_POST['rate'])) ? trim($_POST['rate']) : '';
			}

			if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
				$filter['transfer']['action'] = '';
				$filter['transfer']['rate'] = '';
				$filter['transfer']['booked_date'] = '';
				$filter['transfer']['payment_status'] = '';
			}
			$complaints = $BookingModel->getbookingdetailforuser($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $service_type, $logged_user_id);
			$countlist = $BookingModel->getbookingdetailforuser($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $service_type, $logged_user_id);
			if ($_POST['add_filter'] == 0) {
				$db = \Config\Database::connect();
				$builder1 = $db->table('tbl_booking as l');
				$builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name,CONCAT(d.firstname,' ',d.lastname) as user_name,pa.package_title as package_name ");
				$builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
				$builder1->join('tbl_user as d', 'd.id  = l.user_id');
				$builder1->join('tbl_package as pa', 'pa.id  = l.service_id');
				$builder1->where('l.provider_id', $logged_user_id);
				$builder1->where('l.service_type', $service_type);

				$total_loan_record = $builder1->get()->getResult();
				$total_record = count($total_loan_record);
				// echo json_encode($total_loan_record);die();
			} else {
				$total_record = count($countlist);
			}
			if ($complaints != null) {

				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang("Language.Record Found"),
					'total' => $total_record,
					'info' => $complaints,
				];
			} else {
				$response = [
					'status' => 'Failed',
					'status_code' => 500,
					'messages' => lang("Language.Record Not Found"),
				];
			}
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.User Role Not Found"),
			];
		}


		return $this->respondCreated($response);
	}

	// /list of activities for provider 
	public function activitiesListForProvider()
	{
		$ProviderModel = new ProviderModel();
		$ActivitieModel = new ActivitieModel();

		$user_role = $this->request->getPost('logged_user_role');
		$provider_id = $this->request->getPost('logged_user_id');
		// $user_role = $this->request->getPost('user_role');
		$add_filter = $this->request->getPost('add_filter');

		// Email Validation
		$userdata = $ProviderModel->where('user_role', $user_role)->where("id", $provider_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
			die();
		}

		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;	

		$filter['transfer']['activitie_title'] = '';
		$filter['transfer']['city_loaction'] = '';
		$filter['transfer']['included'] = '';
		$filter['transfer']['pickup_loaction'] = '';
		$filter['transfer']['drop_loaction'] = '';
		$filter['transfer']['accommodations'] = '';
		$filter['transfer']['type_of_activitie'] = '';
		$filter['transfer']['add_filter'] = '';

		if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
			$filter['transfer']['activitie_title'] = (isset($_POST['activitie_title']) && !empty($_POST['activitie_title'])) ? trim($_POST['activitie_title']) : '';
			$filter['transfer']['city_loaction'] = (isset($_POST['city_loaction']) && !empty($_POST['city_loaction'])) ? trim($_POST['city_loaction']) : '';
			$filter['transfer']['included'] = (isset($_POST['included']) && !empty($_POST['included'])) ? trim($_POST['included']) : '';
			$filter['transfer']['drop_loaction'] = (isset($_POST['drop_loaction']) && !empty($_POST['drop_loaction'])) ? trim($_POST['drop_loaction']) : '';
			$filter['transfer']['accommodations'] = (isset($_POST['accommodations']) && !empty($_POST['accommodations'])) ? trim($_POST['accommodations']) : '';
			$filter['transfer']['type_of_activitie'] = (isset($_POST['type_of_activitie']) && !empty($_POST['type_of_activitie'])) ? trim($_POST['type_of_activitie']) : '';
			$filter['transfer']['pickup_loaction'] = (isset($_POST['pickup_loaction']) && !empty($_POST['pickup_loaction'])) ? trim($_POST['pickup_loaction']) : '';
		}

		if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
			$filter['transfer']['activitie_title'] = '';
			$filter['transfer']['city_loaction'] = '';
			$filter['transfer']['included'] = '';
			$filter['transfer']['drop_loaction'] = '';
			$filter['transfer']['accommodations'] = '';
			$filter['transfer']['type_of_activitie'] = '';
			$filter['transfer']['pickup_loaction'] = '';
		}
		$complaints = $ActivitieModel->getproviderActivitie($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $provider_id);
		$countlist = $ActivitieModel->getproviderActivitie($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $provider_id);
		if ($_POST['add_filter'] == 0) {
			$db = \Config\Database::connect();
			$builder1 = $db->table('tbl_activities as l');
			$builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
			$builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
			$builder1 = $builder1->where('l.provider_id', $provider_id);
			$total_loan_record = $builder1->get()->getResult();
			$total_record = count($total_loan_record);
			// echo json_encode($total_loan_record);die();
		} else {
			$total_record = count($countlist);
		}
		if ($complaints != null) {

			$response = [
				'status' => 'success',
				'status_code' => 200,
				'messages' => lang("Language.Record Found"),
				'total' => $total_record,
				'info' => $complaints,
			];
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.Record Not Found"),
			];
		}

		return $this->respondCreated($response);
	}

	// List of booking package by user 
	public function listOfBookingHistory()
	{
		$OtaMoodel = new OtaMoodel();
		$PackageModels = new PackageModels();
		$BookingModel = new BookingModel();
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$add_filter = $this->request->getPost('add_filter');
		$package_title = $this->request->getPost('package_title');
		$ota_id = $this->request->getPost('ota_id');


		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;			
		$service_type = "package";
		// check ota
		$otadata = $OtaMoodel->where("id", $ota_id)->first();
		if (empty($otadata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
			die();
		}


		if ($logged_user_role == "user") {
			// $filter['transfer']['project_title'] = '';
			$filter['transfer']['action'] = '';
			// $filter['transfer']['package_title'] = '';

			$filter['transfer']['add_filter'] = '';
			if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
				$filter['transfer']['action'] = (isset($_POST['action']) && !empty($_POST['action'])) ? trim($_POST['action']) : '';
				// $filter['transfer']['project_title'] = (isset($_POST['project_title']) && !empty($_POST['project_title'])) ? trim($_POST['project_title']) : '';
			}

			if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
				// $filter['transfer']['project_title'] = '';
				$filter['transfer']['action'] = '';
			}
			$complaints = $BookingModel->getbookinghistoryforuser($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $service_type, $logged_user_id, $package_title);
			$countlist = $BookingModel->getbookinghistoryforuser($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $service_type, $logged_user_id, $package_title);
			if ($_POST['add_filter'] == 0) {
				$db = \Config\Database::connect();
				$builder1 = $db->table('tbl_booking as l');
				$builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name,pa.package_title as package_name,pax.name as pax_name,vec.name as vec_name ");
				$builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
				$builder1->join('tbl_package as pa', 'pa.id  = l.service_id');
				// $builder1->join('tbl_pax_master as pax', 'pax.id = l.no_of_pox');
				// $builder1->join('tbl_vehicle_master as vec', 'vec.id = l.cars');
				$builder1->join('tbl_pax_master as pax', 'pax.id = l.no_of_pox', 'left')->where('pa.package_type', 'group');
				$builder1->join('tbl_vehicle_master as vec', 'vec.id = l.cars', 'left')->where('pa.package_type', 'group');
				$builder1->where('l.user_id', $logged_user_id);
				$builder1->where('l.service_type', $service_type);
				$total_loan_record = $builder1->get()->getResult();
				$total_record = count($total_loan_record);
			} else {
				$total_record = count($countlist);
			}
			if ($complaints != null) {

				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang("Language.Record Found"),
					'total' => $total_record,
					'info' => $complaints,
				];
			} else {
				$response = [
					'status' => 'Failed',
					'status_code' => 500,
					'messages' => lang("Language.Record Not Found"),
				];
			}
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.User Role Not Found"),
			];
		}
		return $this->respondCreated($response);
	}
	// Guide verify
	public function verifyGuide()
	{

		$GuideModel = new GuideModel();
		$GuideDocModel = new GuideDocModel();
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$guide_id = $this->request->getPost("guide_id");
		$status = $this->request->getPost("status");
		$reason = $this->request->getPost("reason");

		// check Guide
		$Guidedata = $GuideModel->where("id", $guide_id)->first();
		if (empty($Guidedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Guide Not Found')]);
			die();
		}

		if ($logged_user_role == 'admin') {
			if ($status == "yes") {
				$guide_data = [
					'is_verify' => $status,
					'reason' => 'All good'
				];
				$update_data = $GuideModel->update($guide_id, $guide_data);
				$usermail =  $Guidedata['email'];
				$reason1 =  $guide_data['reason'];
				$firstname =  $Guidedata['firstname'];

				$regsterdata = array('email' => $usermail, 'password' => $reason1, 'username' => $firstname);
				$msg_template = view('emmail_templates/guideverify_yes.php', $regsterdata);
				$subject      = 'Guide is verified';
				$to_email     =  $usermail;
				$abc =  MailSender::sendMail($to_email, $subject, $msg_template, '', '', "umarhaaddons", '');

				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Guide Verified Successfully")
				];
			} else {
				$guide_data = [
					'is_verify' => $status,
					'reason' => $reason
				];
				$update_data = $GuideModel->update($guide_id, $guide_data);
				$usermail =  $Guidedata['email'];
				$reason1 =  $guide_data['reason'];
				$firstname =  $Guidedata['firstname'];
				$regsterdata = array('email' => $usermail, 'reason' => $reason1, 'username' => $firstname);
				$msg_template = view('emmail_templates/guideverify_no.php', $regsterdata);
				$subject      = 'Guide is Not verified';
				$to_email     =  $usermail;

				$abc =  MailSender::sendMail($to_email, $subject, $msg_template, '', '', "umarhaaddons", '');
				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Guide Verified Successfully")
				];
			}
		} else {
			$response = [
				'status' => "Failed",
				'status_code' => 500,
				'messages' => lang("Language.This Api is for admin only")
			];
		}
		return $this->respondCreated($response);
	}

	// List of booking package by user 
	public function listOfGuide()
	{
		$GuideModel = new GuideModel();
		$GuideDocModel = new GuideDocModel();
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$add_filter = $this->request->getPost('add_filter');
		$guide_name = $this->request->getPost('guide_name');
		$guide_email = $this->request->getPost('guide_email');


		$pageNo = $this->request->getPost('page_no');
		$per_page = PER_PAGE;
		$currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
		$page_no        = ( $currentPage - 1 ) * PER_PAGE;			

		if ($logged_user_role == "admin") {
			// $filter['transfer']['project_title'] = '';
			$filter['transfer']['guide_name'] = '';
			$filter['transfer']['guide_email'] = '';


			$filter['transfer']['add_filter'] = '';
			if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
				$filter['transfer']['guide_name'] = (isset($_POST['guide_name']) && !empty($_POST['guide_name'])) ? trim($_POST['guide_name']) : '';
				$filter['transfer']['guide_email'] = (isset($_POST['guide_email']) && !empty($_POST['guide_email'])) ? trim($_POST['guide_email']) : '';
			}

			if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
				$filter['transfer']['guide_name'] = '';
				$filter['transfer']['guide_email'] = '';
			}
			$complaints = $GuideModel->getguidedetail($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1);
			$countlist = $GuideModel->getguidedetail($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0);
			if ($_POST['add_filter'] == 0) {
				$userdata = $GuideModel->select('*')->findAll();
				$total_record = count($userdata);
			} else {
				$total_record = count($countlist);
			}
			if ($complaints != null) {

				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang("Language.Record Found"),
					'total' => $total_record,
					'info' => $complaints,
				];
			} else {
				$response = [
					'status' => 'Failed',
					'status_code' => 500,
					'messages' => lang("Language.Record Not Found"),
				];
			}
		} else {
			$response = [
				'status' => 'Failed',
				'status_code' => 500,
				'messages' => lang("Language.User Role Not Found"),
			];
		}
		return $this->respondCreated($response);
	}
}

/* End of file ListFilter.php */
/* Location: .//C/xampp/htdocs/amtik/app/Controllers/ListFilter.php */