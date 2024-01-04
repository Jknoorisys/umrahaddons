<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\AdminModel;
use App\Models\OtaMoodel;
use App\Models\BookingModel;
use App\Models\UserModels;
use App\Models\PackageModels;
use CodeIgniter\API\ResponseTrait;
use App\Models\MovmentModels;
use App\Models\ImagePackageModels;
use App\Models\VehicleModels;
use App\Models\DayMappingModel;

use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

use Exception;

use Config\Services;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class User extends ResourceController
{

    private $user_id = null;
    private $user_role = null;
    private $token = null;
    private $service;

    public function __construct()
    {
        $this->service  = new Services();
        helper('auth');
        helper('notifications');
        $lang = (isset($_POST) && !empty($_POST)) ? $_POST["language"] : '';
        if (!empty($lang)) {
            $language = \Config\Services::language();
            $language->setLocale($lang);
        } else {
            echo json_encode(['status' => 403, 'messages' => 'language required']);
            die();
        }

        $str = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
        if ($str != 'accessDefine') {
            checkEmptyPost($_POST);
        }

        $db = \Config\Database::connect();
        // Check Authentication
        $this->token = $token = $_POST['authorization'];
        $this->user_id = $user_id = $_POST['logged_user_id'];
        $this->user_role = $user_role = $_POST['logged_user_role'];

        if (!$this->service->getAccessForSignedUser($token, $user_role)) 
		{
			echo json_encode(['status' => 'failed', 'messages' => 'Access denied']);
			die();
		}

        $timezone = "Asia/Kolkata";
        date_default_timezone_set($timezone);
    }

    private $user_excluded_keys = array("password", "token");
    private $access = array("id", "staff_member_id", "created_by", "created_date", "updated_date");

    use ResponseTrait;

    public function getKey()
    {
        return "my_application_secret";
    }

    // Check Authintication
    public function checkAuthentication($token = '', $userid = '', $role = '')
    {
        // $InternalAdminModel = new InternalAdminModel();
        $AdminModel = new AdminModel();
        $UserModels = new UserModels();
        // $Employee = new Employee();
        // $customer = new CustomerModel();

        $key = $this->getKey();
        try {
            $decoded = JWT::decode($token, $key, array("HS256"));
            if ($decoded) {
                $id = $decoded->id;
                if ($role == "admin") {
                    $userdata = $AdminModel->where("token", $token)->where("id", $userid)->first();
                } elseif ($role == "user") {
                    $userdata = $UserModels->where("token", $token)->where("id", $userid)->first();
                }
                // elseif ($role == 2) {
                // 	$userdata = $InternalAdminModel->where("token", $token)->where("id", $userid)->first();
                // } elseif ($role == 5) {
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

    // // get detail of package by user 
    // public function getPackageDetailForUser()
    // {
    //     $UserModels = new UserModels();
    //     $PackageModels = new PackageModels();
    //     $logged_user_id = $this->request->getPost("logged_user_id");
    //     $logged_user_role = $this->request->getPost("logged_user_role");
    //     $package_id = $this->request->getPost('package_id');
    //     $MovmentModels = new MovmentModels();
    //     $ImagePackageModels = new ImagePackageModels();
    //     $VehicleModels = new VehicleModels();
    //     $active = "active";


    //     // check user
    //     $userdata = $UserModels->where("id", $logged_user_id)->first();
    //     if (empty($userdata)) {
    //         echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
    //         die();
    //     }

    //     // Package Validation
    //     $packagedata = $PackageModels->where("id", $package_id)->where("status_by_admin", $active)->where("status", $active)->first();
    //     if (empty($packagedata)) {
    //         echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
    //         die();
    //     }


    //     $db = \Config\Database::connect();
    //     $builder1 = $db->table('tbl_package as l');
    //     $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
    //     $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
    //     $builder1->where('l.id', $package_id);
    //     $data = $builder1->get()->getResult();
    //     $image_data =  $ImagePackageModels->where("package_id", $package_id)->findAll();
    //     $Vehicle_data =  $VehicleModels->where("package_id", $package_id)->findAll();
    //     $Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();
    //     if (!empty($data)) {
    //         $response = [
    //             'status' => "success",
    //             'status_code' => 200,
    //             'messages' => lang('Language.Package Details'),
    //             'Package_data' => $data,
    //             'Image_data' => $image_data,
    //             'Vehicle_data' => $Vehicle_data,
    //             'Movment_data' => $Movment_data,
    //         ];
    //     } else {
    //         $response = [
    //             'status' => "failed",
    //             'status_code' => 404,
    //             'messages' => lang('Language.Package Details Not Found'),
    //         ];
    //     }
    //     return $this->respondCreated($response);
    // }

    //  booking package 
    public function packageBookingUser()
    {
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        $BookingModel = new BookingModel();
        $VehicleModels = new VehicleModels();
        $OtaMoodel = new OtaMoodel();

        $ota_id = $this->request->getPost('ota_id');
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $package_id = $this->request->getPost('package_id');
        $pax_id = $this->request->getPost('pax_id');
        $date = $this->request->getPost('date');
        $user_pax = $this->request->getPost('user_pax');

        $active = "active";

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->where('status',$active)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        $packagedata = $PackageModels->where("id", $package_id)->where("status_by_admin", $active)->where("status", $active)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        $userdata = $UserModels->where("id", $logged_user_id)->where("status", $active)->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
            die();
        }

        $provider_id = $packagedata['provider_id'];
        $car_data = $VehicleModels->where("id", $pax_id)->where("package_id", $package_id)->where("status", $active)->first();
        $rate = $car_data['rate'];
        $no_of_pox = $car_data['no_of_pox_id'];
        $cars = $car_data['vehicle_id'];

        if ($logged_user_role == "user") {
            $data = [
                "service_type" => "package",
                "service_id" => $package_id,
                "user_id" => $logged_user_id,
                "user_role" => $logged_user_role,
                "from_date" => date('Y-m-d', strtotime($date)),
                "ota_id" => $ota_id,
                'cars' => $cars,
                'user_pax' => $user_pax,
                'rate' => $rate,
                "no_of_pox" => $no_of_pox,
                "provider_id" => $provider_id,
                "booked_time" => date("h:i:sa"),
                "booked_date" => date("Y-m-d")
            ];

            if ($BookingModel->insert($data)) {
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang("Language.Booking Done Successfully")
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 404,
                    'messages' => lang('Language.Failed To Booked Package')
                ];
            }
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.User Role Not Found')
            ];
        }
        return $this->respondCreated($response);
    }


    // User booking detail for user 
    public function bookingPackageDetailById()
    {
        $UserModels = new UserModels();
        $OtaMoodel = new OtaMoodel();
        $BookingModel = new BookingModel();
        $VehicleModels = new VehicleModels();
        $ImagePackageModels = new ImagePackageModels();
        $MovmentModels = new MovmentModels();
        $DayMappingModel = new DayMappingModel();
        $PackageModels = new PackageModels();


        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $booking_id = $this->request->getPost('booking_id');
        $ota_id = $this->request->getPost('ota_id');


        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->where('status','active')->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        // check user
        $userdata = $UserModels->where("id", $logged_user_id)->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
            die();
        }

        // check booking
        $booking_data = $BookingModel->where("id", $booking_id)->where('user_id', $logged_user_id)->first();
        if (empty($booking_data)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Booking Not Found')]);
            die();
        }

        if ($logged_user_role == 'user') {
            $bookingdatas = $BookingModel->where("id", $booking_id)->first();
            $package_id = $bookingdatas['service_id'];

            $packageData = $PackageModels->where("id", $package_id)->first();

            // Booing data
            $db = \Config\Database::connect();
            $builder = $db->table('tbl_booking as b');
            $builder->select("b.*");

            if ($packageData && $packageData['service_type'] == 'group') {
                $builder->join('tbl_pax_master as pax', 'pax.id  = b.no_of_pox');
                $builder->join('tbl_vehicle_master as vec', 'vec.id  = b.cars');
                $builder->select("pax.name as pax_name,vec.name as vec_name");
            }
            
            $builder->where('b.id', $booking_id);
            $bookingdata = $builder->get()->getResult();

            // package data
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_package as l');
            $builder1->select("l.package_title,l.city_loaction,l.main_img,l.included,l.not_included,l.pickup_loaction,l.drop_loaction,l.return_policy,CONCAT(c.firstname,' ',c.lastname) as provider_name, c.supporter_no ");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->where('l.id', $package_id);
            $data = $builder1->get()->getResult();

            // image data
            $image_data =  $ImagePackageModels->where("package_id", $package_id)->findAll();

            // movement data
            $Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();


            foreach ($Movment_data as $key => $value) {
                $inventatory_detail = $DayMappingModel->where('movement_id', $value['id'])->where('package_id', $value['package_id'])->findAll();

                $Movment_data[$key]['inventatory_detail'] = $inventatory_detail;
            }

            if (!empty($bookingdata)) {
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang('Language.Booking Details'),
                    'booking_data' => $bookingdata,
                    'Package_data' => $data,
                    'Image_data' => $image_data,
                    'Movment_data' => $Movment_data,
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 404,
                    'messages' => lang('Language.Booking Details Not Found'),
                ];
            }
            return $this->respondCreated($response);
        }
    }
} // class end

/* End of file User.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/User.php */