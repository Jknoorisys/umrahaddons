<?php

namespace App\Controllers;

use App\Models\OtaMoodel;
use App\Models\ProviderModel;
use App\Models\UserModels;
use App\Models\AdminModel;
use App\Models\PackageModels;
use App\Models\BookingModel;
use App\Models\CountryModel;

use App\Models\StateModel;

use App\Models\City;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;
use Exception;

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Dashboard extends ResourceController
{

    private $user_id = null;
	private $user_role = null;
	private $token = null;
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
        $this->token = $token = $_POST['authorization'];
        $this->user_id = $user_id = $_POST['logged_user_id'];
        $this->user_role = $user_role = $_POST['logged_user_role'];

        $token = $_POST['authorization'];
        $user_id = $_POST['logged_user_id'];
        $user_role = $_POST['logged_user_role'];

        // if (!$this->service->getAccessForSignedUser($token, $user_role)) 
		// {
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

    //  master data for  all
    public function masterDataForAll()
    {
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");
        // $AssingInternalModel = new AssingInternalModel();

        $db = \Config\Database::connect();

        // Total city
        $builder = $db->table('cities');
        $builder->select('*');
        $allcitys = $builder->get()->getResult();

        // Total statw
        $builder = $db->table('states');
        $builder->select('*');
        $allstate = $builder->get()->getResult();

        // Total country
        $allcountry = $db->table('countries');
        $builder->select('*');
        $allcountry = $builder->get()->getResult();


        $info = [
            'allcitys' => $allcitys,
            'allstate' => $allstate,
            'allcountry' => $allcountry,
        ];
        $response = [
            'status' => 'success',
            'messages' => lang("Language.Record Found"),
            'info' => $info,
        ];
        return $this->respond($response);
    }

    // All city 
    public function Allcity()
    {
        $CityModel = new City();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");
        $state_id = $this->request->getPost("state_id");

        // $AssingInternalModel = new AssingInternalModel();

        $citydata = $CityModel->where('state_id', $state_id)->findAll();

        if (!empty($citydata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.City Details'),
                'Package_data' => $citydata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.City Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // All State
    public function AllState()
    {
        $StateModel = new StateModel();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");
        $country_id = $this->request->getPost("country_id");

        // $AssingInternalModel = new AssingInternalModel();

        $stateData = $StateModel->where('country_id', $country_id)->findAll();
        //    echo json_encode($stateData);die();
        if (!empty($stateData)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.State Details'),
                'Package_data' => $stateData
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.State Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // All Country
    public function AllCountry()
    {
        $CountryModel = new CountryModel();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");
        // $AssingInternalModel = new AssingInternalModel();

        $countryDAta = $CountryModel->select("*")->findAll();

        if (!empty($countryDAta)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Countries Details'),
                'Package_data' => $countryDAta
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Countries Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // arab citys
    public function arabCity()
    {
        $CountryModel = new CountryModel();
        $StateModel = new StateModel();
        $CityModel = new City();

        $db = \Config\Database::connect();

        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('states');
        $builder->select('GROUP_CONCAT(`id`) as state_id');
        $builder->where('country_id', '191');
        $allcitys = $builder->get()->getrow();
        $array = explode(',', $allcitys->state_id);


        $builder = $db->table('cities as c');
        $builder->select('c.*');
        $builder->whereIn('state_id', $array);
        $arabcity = $builder->get()->getResult();


        if (!empty($arabcity)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.City Details'),
                'arab_city' => $arabcity
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.City Details Not Found'),
            ];
        }
        return $this->respond($response);
    }


    //  included Master
    public function includedMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_included_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Included Details'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Included Details Not Found'),
            ];
        }
        return $this->respond($response);
    }


    // Not included Master
    public function notIncludedMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_not_included_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.NOT Included Details'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.NOT Included Details Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // ideaol Master
    public function ideaolMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_ideal_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Ideal Master'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Ideal Master Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // no of pax for provider
    public function paxMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_pax_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Pax Master'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Ideal Master Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // no of vech for provider
    public function vehicleMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_vehicle_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Vehicle Master'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Ideal Master Not Found'),
            ];
        }
        return $this->respond($response);
    }


    // service master 
    public function serviceMaster()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost("logged_user_id");
        $role = $this->request->getPost("logged_user_role");

        $builder = $db->table('tbl_service_master');
        $builder->select('*');
        $includeddata = $builder->get()->getResult();


        if (!empty($includeddata)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Service Master'),
                'info' => $includeddata
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Service Master Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // PROVIDER DASHBOARD API - 11 OCT 2022
    public function providerDashboard()
    {
        $service   =  new Services();
        try 
        {
            $db = db_connect();
            $info = [];

            // Package
            $info['total_packages'] =  $db->table('tbl_package')->where('provider_id', $_POST['logged_user_id'])->where('status','active')->countAllResults();
            $info['total_package_bookings'] =  $db->table('tbl_booking')->where('provider_id', $_POST['logged_user_id'])->where('booking_status_user', 'confirm')->countAllResults();
            $info['total_package_completed_bookings'] =  $db->table('tbl_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'accepted')->countAllResults();
            $info['total_package_pending_bookings'] =  $db->table('tbl_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'pending')->countAllResults();

            // MEALS
            $info['total_meals'] =  $db->table('tbl_meals')->where('provider_id', $_POST['logged_user_id'])->where('status !=','0')->where('status !=','deleted')->countAllResults();
            $info['total_meals_bookings'] =  $db->table('meals_booking')->where('provider_id', $_POST['logged_user_id'])->countAllResults();
            $info['total_meals_completed_bookings'] =  $db->table('meals_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'accepted')->countAllResults();
            $info['total_meals_pending_bookings'] =  $db->table('meals_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'pending')->countAllResults();

            // SABEEL
            $info['total_sabeel'] =  $db->table('tbl_sabeel')->where('provider_id', $_POST['logged_user_id'])->countAllResults();
            $info['total_sabeel_bookings'] =  $db->table('tbl_sabeel_booking')->where('provider_id', $_POST['logged_user_id'])->countAllResults();
            $info['total_sabeel_completed_bookings'] =  $db->table('tbl_sabeel_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'accepted')->countAllResults();
            $info['total_sabeel_pending_bookings'] =  $db->table('tbl_sabeel_booking')->where('provider_id', $_POST['logged_user_id'])->where('payment_status', 'completed')->where('booking_status', 'pending')->countAllResults();
            
              // Full Package
              $info['total_full_package'] =  $db->table('tbl_full_package')->where('provider_id', $_POST['logged_user_id'])->where('status !=', '2')->countAllResults();
              $info['total_full_package_bookings'] =  $db->table('tbl_full_package_enquiry')->join('tbl_full_package', 'tbl_full_package_enquiry.full_package_id = tbl_full_package.id')->where('tbl_full_package.provider_id', $_POST['logged_user_id'])->countAllResults();
              $info['total_full_package_completed_bookings'] =  $db->table('tbl_full_package_enquiry')->join('tbl_full_package', 'tbl_full_package_enquiry.full_package_id = tbl_full_package.id')->where('tbl_full_package.provider_id', $_POST['logged_user_id'])->where('booking_status', 'accepted')->countAllResults();
              $info['total_full_package_pending_bookings'] =  $db->table('tbl_full_package_enquiry')->join('tbl_full_package', 'tbl_full_package_enquiry.full_package_id = tbl_full_package.id')->where('tbl_full_package.provider_id', $_POST['logged_user_id'])->where('booking_status', 'pending')->countAllResults();

            return $service->success([
                'message'       =>  Lang('Language.details_success'),
                'data'          =>  $info
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.some_things_error'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function otaDashboard()
    {
        $service   =  new Services();
        try 
        {
            $db = db_connect();
            $info = [];

            // PACKAGE
            $info['total_package_booking'] =  $db->table('tbl_booking')->where('ota_id', $_POST['logged_user_id'])->countAllResults();

            $pcamt = $db->table('tbl_booking')->where(["ota_id" => $_POST['logged_user_id']])->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;
            $info['pending_commission_amt_package'] = ($pcamt!=null)?round($pcamt,2):'0';
            
            
            $tcamt = $db->table('tbl_booking')->where("ota_id", $_POST['logged_user_id'])->where("ota_payment_status", 'paid')->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;
            $info['total_commission_amt_package'] = ($tcamt!=null)?round($tcamt,2):'0';


            // MEALS
            $info['total_meals_booking'] =  $db->table('meals_booking')->where('ota_id', $_POST['logged_user_id'])->countAllResults();      

            $pcamtm = $db->table('meals_booking')->where(["ota_id" => $_POST['logged_user_id']])->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;
            $info['pending_commission_amt_meals'] = ($pcamtm!=null)?round($pcamtm,2):'0';    

            $tcamtm = $db->table('meals_booking')->where("ota_id", $_POST['logged_user_id'])->where("ota_payment_status", 'paid')->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;
            $info['total_commission_amt_meals'] = ($tcamtm!=null)?round($tcamtm,2):'0';

            // SABEEL
            $info['total_sabeel_booking'] =  $db->table('tbl_sabeel_booking')->where('ota_id', $_POST['logged_user_id'])->countAllResults();

            $pcamts = $db->table('tbl_sabeel_booking')->where(["ota_id" => $_POST['logged_user_id']])->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;
            $info['pending_commission_amt_sabeel'] = ($pcamts!=null)?round($pcamts,2):'0';      

            $tcamts = $db->table('tbl_sabeel_booking')->where("ota_id", $_POST['logged_user_id'])->where("ota_payment_status", 'paid')->select('SUM(ota_commision_amount) as total')->get()->getRow()->total;  
            $info['total_commission_amt_sabeel'] = ($tcamts!=null)?round($tcamts,2):'0';
            
            return $service->success([
                'message'       =>  Lang('Language.details_success'),
                'data'          =>  $info
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
            
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.some_things_error'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function adminDashboard()
    {
        $service   =  new Services();
        try 
        {
            $db = db_connect();
            $info = [];

            // PACKAGE
            $info['total_providers'] =  $db->table('tbl_provider')->countAllResults();
            $info['total_ota'] =  $db->table('tbl_ota')->countAllResults();
            $info['total_meals'] =  $db->table('tbl_meals')->where('status !=','deleted')->countAllResults();
            $info['total_sabeel'] =  $db->table('tbl_sabeel')->where('status !=','0')->countAllResults();
            $info['total_package'] =  $db->table('tbl_package')->where('status','active')->countAllResults();
            $info['total_full_package'] =  $db->table('tbl_full_package')->where('status !=','2')->countAllResults();

            $info['total_package_bookings'] =  $db->table('tbl_booking')->where('booking_status_user','confirm')->countAllResults();
            $info['total_sabeel_bookings'] =  $db->table('tbl_sabeel_booking')->where('booking_status_user','confirm')->countAllResults();
            $info['total_meals_bookings'] =  $db->table('meals_booking')->where('booking_status_user','confirm')->countAllResults();
            $info['total_full_package_bookings'] =  $db->table('tbl_full_package_enquiry')->countAllResults();
            $info['total_visa_bookings'] =  $db->table('tbl_visa_enquiry')->countAllResults();
            $info['total_transport_bookings'] =  $db->table('tbl_transport_enquiry')->countAllResults();


            $info['pending_package_bookings'] =  $db->table('tbl_booking')->where('booking_status','pending')->countAllResults();
            $info['pending_sabeel_bookings'] =  $db->table('tbl_sabeel_booking')->where('booking_status','pending')->countAllResults();
            $info['pending_meals_bookings'] =  $db->table('meals_booking')->where('booking_status','pending')->countAllResults();
            $info['pending_full_package_bookings'] =  $db->table('tbl_full_package_enquiry')->where('booking_status', 'pending')->countAllResults();
            $info['pending_visa_bookings'] =  $db->table('tbl_visa_enquiry')->where('booking_status', 'pending')->countAllResults();
            $info['pending_transport_bookings'] =  $db->table('tbl_transport_enquiry')->where('booking_status', 'pending')->countAllResults();

            $info['accepted_package_bookings'] =  $db->table('tbl_booking')->where('booking_status','accepted')->countAllResults();
            $info['accepted_sabeel_bookings'] =  $db->table('tbl_sabeel_booking')->where('booking_status','accepted')->countAllResults();
            $info['accepted_meals_bookings'] =  $db->table('meals_booking')->where('booking_status','accepted')->countAllResults();
            $info['accepted_full_package_bookings'] =  $db->table('tbl_full_package_enquiry')->where('booking_status', 'accepted')->countAllResults();
            $info['accepted_visa_bookings'] =  $db->table('tbl_visa_enquiry')->where('booking_status', 'accepted')->countAllResults();
            $info['accepted_transport_bookings'] =  $db->table('tbl_transport_enquiry')->where('booking_status', 'accepted')->countAllResults();

            $info['rejected_package_bookings'] =  $db->table('tbl_booking')->where('booking_status','rejected')->countAllResults();
            $info['rejected_sabeel_bookings'] =  $db->table('tbl_sabeel_booking')->where('booking_status','rejected')->countAllResults();
            $info['rejected_meals_bookings'] =  $db->table('meals_booking')->where('booking_status','rejected')->countAllResults();
            $info['rejected_full_package_bookings'] =  $db->table('tbl_full_package_enquiry')->where('booking_status', 'rejected')->countAllResults();
            $info['rejected_visa_bookings'] =  $db->table('tbl_visa_enquiry')->where('booking_status', 'rejected')->countAllResults();
            $info['rejected_transport_bookings'] =  $db->table('tbl_transport_enquiry')->where('booking_status', 'rejected')->countAllResults();

            return $service->success([
                'message'       =>  Lang('Language.details_success'),
                'data'          =>  $info
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
            
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.some_things_error'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    
}

/* End of file Dashboard.php */
/* Location: .//C/xampp/htdocs/amtik/app/Controllers/Dashboard.php */