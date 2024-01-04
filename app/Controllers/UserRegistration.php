<?php

namespace App\Controllers;

use App\Database\Migrations\TblTransportEnquiry;
use App\Models\OtaMoodel;
use App\Models\ProviderModel;
use App\Models\UserModels;
use App\Models\AdminModel;
use App\Models\PackageModels;
use App\Models\BookingModel;
use App\Models\CountryModel;
use App\Models\MovmentModels;
use App\Models\ImagePackageModels;
use App\Models\VehicleModels;
use App\Models\DayMappingModel;
use App\Models\VehicleMasterModel;
use App\Models\PaxMasaterModel;
use App\Models\GuideModel;
use App\Models\GuideDocModel;
// use App\Libraries\MailSender;
use App\Libraries\MailSender;

use App\Models\StateModel;

use App\Models\City;
use App\Models\FullPackageEnquiry;
use App\Models\MealsBookingModel;
use App\Models\PackageInquiryModel;
use App\Models\SabeelBookingModel;
use App\Models\TransportModel;
use App\Models\VisaEnquiry;
use App\Models\ZiyaratPoints;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use \Firebase\JWT\JWT;
use Exception;

use Config\Services;
use RuntimeException;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class UserRegistration extends ResourceController
{

    private $service;

    public function __construct()
    {
        $this->service  = new Services();
        helper('auth');
        // $UsersModel = new UsersModel();
        // $lang = $_POST["language"];
        // if (!empty($lang)) {
        //     $language = \Config\Services::language();
        //     $language->setLocale($lang);
        // } else {
        //     echo json_encode(['status' => 403, 'messages' => 'language required']);
        //     die();
        // }

        // checkEmptyPost($_POST);

        $db = \Config\Database::connect();
        // Check Authentication
        // // $this->token = $token = $_POST['authorization'];
        // $this->user_id = $user_id = $_POST['logged_user_id'];
        // $this->user_role = $user_role = $_POST['logged_user_role'];

        // $token = $_POST['authorization'];
        // $user_id = $_POST['logged_user_id'];
        // $user_role = $_POST['logged_user_role'];


    }

    public function getKey()
    {
        return "my_application_secret";
    }

    // User registration
    public function userregistration()
    {
        $email = \Config\Services::email();
        $UserModels = new UserModels();
        $OtaMoodel = new OtaMoodel();

        $ota_id = $this->request->getPost('ota_id');

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        // Email Validation
        $userdata = $UserModels->where("email", $this->request->getPost("email"))->first();
        if (!empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Already Exists')]);
            die();
        }

        $data = [
            "firstname" => $this->request->getPost("firstname"),
            "lastname" => $this->request->getPost("lastname"),
            "email" => $this->request->getPost("email"),
            "plain_password" => $this->request->getPost("password"),
            "password" => password_hash($this->request->getPost("password"), PASSWORD_DEFAULT),
            "mobile" => $this->request->getPost("mobile"),
            "user_role" => "user",
            "gender" => $this->request->getPost("gender"),
            "city" => $this->request->getPost("city"),
            "state" => $this->request->getPost("state"),
            "country" => $this->request->getPost("country"),
            "zip_code" => $this->request->getPost("zip_code"),
            "created_by_id" => $ota_id,
            "created_by_role" => "ota",
            "created_by" => "user",
        ];
        // echo json_encode($data);die();
        if (!checkEmptyPost($data)) {
            if ($UserModels->insert($data)) {
                // Send Email
                $mail = \Config\Services::email();
                $usermail =  $data['email'];
                $password =  $data['plain_password'];
                $firstname =  $data['firstname'];

                $mail->setTo($usermail);
                $mail->setFrom('noori.developer@gmail.com', 'Umrah Plus');
                $data = array('email' => $usermail, 'password' => $password, 'username' => $firstname);
                $msg = view('emmail_templates/forgotpassword.php', $data);
                $mail->setSubject('Register Users');
                $mail->setMessage($msg);
                // $abc = $mail->send();
                MailSender::sendMail($usermail, 'Register Users', $msg , '', '', "Umrah Plus", '');
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang("Language.Users Create Successfully")
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 500,
                    'messages' => lang("Language.Failed to Create")
                ];
            }
            return $this->respondCreated($response);
        }
    }


    // All city 
    public function AllcityForUser()
    {
        $CityModel = new City();
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
    public function AllStateForUser()
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
    public function AllCountryForUser()
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

    // get detail of package by user 
    public function getPackageDetailForUser()
    {
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        $package_id = $this->request->getPost('package_id');
        $MovmentModels = new MovmentModels();
        $ImagePackageModels = new ImagePackageModels();
        $VehicleModels = new VehicleModels();
        $DayMappingModel = new DayMappingModel();
        $OtaMoodel = new OtaMoodel();
        $ZiyaratPoints = new ZiyaratPoints();

        $ota_id = $this->request->getPost('ota_id');

        $active = "active";

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }




        // Package Validation
        $packagedata = $PackageModels->where("id", $package_id)->where("status_by_admin", $active)->where("status", $active)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        $points = explode(',',$packagedata['ziyarat_points']);
        $ziyarat_points = $ZiyaratPoints->whereIn('tbl_ziyarat_points.id',$points)
                                ->join('tbl_city_master as c', 'c.id  = tbl_ziyarat_points.city_id')
                                ->where('tbl_ziyarat_points.status', '1')
                                ->select('tbl_ziyarat_points.*, c.name as city_name, c.image as city_image')->findAll();

        $db = \Config\Database::connect();
        $builder1 = $db->table('tbl_package as l');
        $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
        $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
        $builder1->where('l.id', $package_id);
        $data = $builder1->get()->getResult();
        $image_data =  $ImagePackageModels->where("package_id", $package_id)->findAll();
        // $Vehicle_data =  $VehicleModels->where("package_id", $package_id)->findAll();
        // $Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();
        $Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();
        // fetching record of  Vechile  data
        $builder = $db->table('tbl_package_vehicle as pv');
        $builder->select('pv.*,pax.name as pax_name,pax.min_pax,pax.max_pax,vech.name as vech_name');
        $builder->join('tbl_pax_master as pax', 'pax.id  = pv.no_of_pox_id');
        $builder->join('tbl_vehicle_master as vech', 'vech.id  = pv.vehicle_id');
        $builder->where('pv.package_id', $package_id);
        $builder->where('pv.status', 'active');
        $Vehicle_data = $builder->get()->getResult();
        // echo json_encode($Movment_data);die();
        foreach ($Movment_data as $key => $value) {
            $inventatory_detail = $DayMappingModel->where('movement_id', $value['id'])->where('package_id', $value['package_id'])->findAll();

            $Movment_data[$key]['inventatory_detail'] = $inventatory_detail;
        }
        if (!empty($data)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang('Language.Package Details'),
                'Package_data' => $data,
                'Image_data' => $image_data,
                'Vehicle_data' => $Vehicle_data,
                'Movment_data' => $Movment_data,
                'ziyarat_points' => $ziyarat_points,
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.Package Details Not Found'),
            ];
        }
        return $this->respondCreated($response);
    }

    // ideaol Master
    public function ideaolMasterForUser()
    {
        $db = \Config\Database::connect();


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

    // list of package   for customer 
    public function packageListForUser()
    {
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        $OtaMoodel = new OtaMoodel();

        $ota_id = $this->request->getPost('ota_id');
        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }
        // $logged_user_id = $this->request->getPost("logged_user_id");
        // $logged_user_role = $this->request->getPost("logged_user_role");
        $page_no = $this->request->getPost('page_no');
        $add_filter = $this->request->getPost('add_filter');
        $per_page = PER_PAGE;
        $active = "active";
        $min_val = $this->request->getPost('min_val');

        $filter['transfer']['search_word'] = '';
        $filter['transfer']['package_title'] = '';
        $filter['transfer']['city_loaction'] = '';
        $filter['transfer']['package_amount'] = '';
        $filter['transfer']['ideal_for'] = '';

        $filter['transfer']['add_filter'] = '';
        if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
            $filter['transfer']['search_word'] = (isset($_POST['search_word']) && !empty($_POST['search_word'])) ? trim($_POST['search_word']) : '';
            $filter['transfer']['package_title'] = (isset($_POST['package_title']) && !empty($_POST['package_title'])) ? trim($_POST['package_title']) : '';
            $filter['transfer']['city_loaction'] = (isset($_POST['city_loaction']) && !empty($_POST['city_loaction'])) ? trim($_POST['city_loaction']) : '';
            $filter['transfer']['package_amount'] = (isset($_POST['package_amount']) && !empty($_POST['package_amount'])) ? trim($_POST['package_amount']) : '';
            $filter['transfer']['ideal_for'] = (isset($_POST['ideal_for']) && !empty($_POST['ideal_for'])) ? trim($_POST['ideal_for']) : '';
        }

        if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
            $filter['transfer']['search_word'] = '';
            $filter['transfer']['package_title'] = '';
            $filter['transfer']['city_loaction'] = '';
            $filter['transfer']['package_amount'] = '';
            $filter['transfer']['ideal_for'] = '';
        }
        $complaints = $PackageModels->getuserpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $active, $min_val);
        $countlist = $PackageModels->getuserpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $active, $min_val);
        if ($_POST['add_filter'] == 0) {
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_package as l');
            $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->where('l.status', $active);
            $builder1->where('l.status_by_admin ', $active);
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

    // arab citys for user
    public function arabCityForUser()
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

    //Check Number of pax details
    public function paxDetailsForUser()
    {
        $PackageModels = new PackageModels();
        $VehicleModels = new VehicleModels();
        $PaxMasaterModel = new PaxMasaterModel();
        $VehicleMasterModel = new VehicleMasterModel();

        $db = \Config\Database::connect();
        $num_of_pax = $this->request->getPost("num_of_pax");
        $package_id = $this->request->getPost("package_id");

        $pax_vech_data = $VehicleModels->select()->where('package_id', $package_id)->findAll();


        // $builder = $db->table('tbl_package_vehicle');
        // $builder->select('GROUP_CONCAT(`no_of_pox_id`) as pax_id');
        // $builder->where('package_id', $package_id);
        // $allpaxId = $builder->get()->getrow();
        // $array = explode(',', $allpaxId->pax_id);
        // echo json_encode($array);die();



        // $builder = $db->table('tbl_pax_master');
        // $builder->select('name');
        // $builder->whereIn('id', $array);
        // $arabcity = $builder->get()->getResult();

        // foreach ($arabcity as $key => $value) {

        //     if ($num_of_pax >= trim($value->name, '1 To')) {
        //         echo 'success';
        //     } else {
        //         echo 'false';
        //     }
        //     // if()
        // }
        // echo json_encode($Movment_data);die();



        if (!empty($arabcity)) {
            // $response = [
            //     'status' => "success",
            //     'status_code' => 200,
            //     'messages' => lang('Language.City Details'),
            //     'arab_city' => $arabcity
            // ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.City Details Not Found'),
            ];
        }
        return $this->respond($response);
    }

    // list of package   for customer 
    public function packageListSearchForUser()
    {
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        // $logged_user_id = $this->request->getPost("logged_user_id");
        // $logged_user_role = $this->request->getPost("logged_user_role");
        $page_no = $this->request->getPost('page_no');
        $add_filter = $this->request->getPost('add_filter');
        $per_page = PER_PAGE;
        $active = "active";



        $filter['transfer']['search_word'] = '';

        $filter['transfer']['add_filter'] = '';
        if (isset($_POST['add_filter']) &&  $_POST['add_filter'] == 1) {
            $filter['transfer']['search_word'] = (isset($_POST['search_word']) && !empty($_POST['search_word'])) ? trim($_POST['search_word']) : '';
        }

        if (isset($_POST['add_filter']) && $_POST['add_filter'] == 0) {
            $filter['transfer']['search_word'] = '';
        }
        $complaints = $PackageModels->getusersearchpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 1, $active);
        $countlist = $PackageModels->getusersearchpackage($filter['transfer'], $per_page, $page_no, $add_filter, $abc = 0, $active);
        if ($_POST['add_filter'] == 0) {
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_package as l');
            $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->where('l.status', $active);
            $builder1->where('l.status_by_admin ', $active);
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

    // OTP verification
    public function otpVerification()
    {
        $UserModels = new UserModels();
        $email_id = $this->request->getPost("email_id");
        $otp = $this->request->getPost("otp");

        $device_token = $this->request->getPost('device_token');
        $device_type = $this->request->getPost('device_type');

        $userdata = $UserModels->where("email", $email_id)->where("status", "Active")->first();
        if (!empty($userdata)) {
            $otp_check = $UserModels->where("email", $email_id)->where("otp", $otp)->first();
            if (!empty($otp_check)) {
                $key = $this->getKey();
                $payload = array(
                    "role" => 'user',
                    "id" => $userdata['id'],
                    "date" => date('Y-m-d'),
                );
                // $token = JWT::encode($payload, $key);

                $token = $this->service->getSignedAccessTokenForUser('user', $userdata['id']);

                $UserModels->update($userdata['id'], ['token' => $token]);
                // Get Info
                $info = $UserModels->where('id', $userdata['id'])->first();

                $UserModels->update($userdata['id'], ['otp' => '', 'device_type'=>$device_type, 'device_token'=>$device_token]);

                // PUSH NOTIFICATION
                helper('notifications');
                $title = "OTP Verification";
                $message = "Your account has been successfully verified.";
                $fmc_ids = array($info['device_token']);
                
                $notification = array(
                    'title' => $title ,
                    'message' => $message,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                    'date' => date('Y-m-d H:i'),
                );
                if($info['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
                // EnD

                // $info = array_diff_key($info, array_flip($this->user_excluded_keys));
                $response = [
                    'status' => 'success',
                    'status_code' => 200,
                    'messages' => lang('Language.User logged In successfully'),
                    'info' => $info
                ];
            } else {
                $response = [
                    'status' => 'failed',
                    'status_code' => 500,
                    'messages' => lang('Language.OTP Not Match')
                ];
            }
        } else {
            $response = [
                'status' => 'failed',
                'status_code' => 500,
                'messages' => lang('Language.User not found or Inactive')
            ];
        }
        return $this->respondCreated($response);
    }

    // GENERATE OTP FUNCTION 
    public function generateNumericOTP($n)
    {
        $generator = "135792468";
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        return $result;
    }

    // new customer registration and login api with email id
    public function userRegtLogin()
    {
        $email = \Config\Services::email();
        $UserModels = new UserModels();
        $OtaMoodel = new OtaMoodel();

        $ota_id = $this->request->getPost('ota_id');
        $email_id = $this->request->getPost('email_id');

        $device_token = $this->request->getPost('device_token');
        $device_type = $this->request->getPost('device_type');

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        // check user 
        $userdata = $UserModels->where("email", $email_id)->first();
        if (!empty($userdata)) {
            $otp = $this->generateNumericOTP(6);
            $updateuser = [
                'otp' => $otp,
                'ota_id' => $ota_id
            ];
            $res = $UserModels->update($userdata['id'], $updateuser);
            
            $data = array('email' => $email_id, 'otp' => $otp);
            $msg_template = view('emmail_templates/userotp.php', $data);
            $subject      = 'Login OTP';
            $to_email     =  $email_id;

            // $abc =  MailSender::sendMail($to_email, $subject, $msg_template , '', '', "umarhaaddons", '');
            $filename = "";
            $send     = sendEmail($to_email, $subject, $msg_template,$filename);
            $response = [
                'status' => 'success',
                'status_code' => 200,
                'messages' => lang('Language.OTP Send successfully'),
                // 'otp' => $otp,
            ];
            return $this->respond($response);
        } else {
            $otp = $this->generateNumericOTP(6);
            $newuser = [
                'email' => $email_id,
                'otp' => $otp,
                'ota_id' => $ota_id,
                'created_by_id' => $ota_id,
                'user_role' => 'user',
                'created_by_role' => 'ota',
                'device_type' => $device_type,
                'device_token' => $device_token,
            ];
            $UserModels->insert($newuser);
            $data = array('email' => $email_id, 'otp' => $otp);
            $msg_template = view('emmail_templates/userotp.php', $data);
            $subject      = 'Login OTP';
            $to_email     =  $email_id;

            $filename = "";
            $send     = sendEmail($to_email, $subject, $msg_template,$filename);
            // PUSH NOTIFICATION
            helper('notifications');
            $userinfo = $UserModels->where("email", $email_id)->first();
            $title = "Registration";
            $message = "Thanks for registering with Umrah Plus.";
            $fmc_ids = array($userinfo['device_token']);
            
            $notification = array(
                'title' => $title ,
                'message' => $message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                'date' => date('Y-m-d H:i'),
            );
            if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
            // EnD

            $response = [
                'status' => "success",
                'status_code' => 200,
                // 'otp' => $otp,
                'messages' => lang("Language.Users Create Successfully")
            ];
            return $this->respond($response);
        }
        $response = [
            'status' => 'failed',
            'status_code' => 500,
            'messages' => lang('Language.User not found or Inactive')
        ];
        return $this->respond($response);
    }

    // new customer registration and login api with mobile number
    public function userRegtLoginwithMobile()
    {
        $service        =  new Services();
        $service->cors();

        $UserModels = new UserModels();
        $OtaMoodel = new OtaMoodel();

        $db = \Config\Database::connect();

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'ota_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'country_code' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'mobile' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            // 'device_token' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
            'device_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
        ];

        if(!$this->validate($rules)) {
            return $service->fail(
                [
                    'errors'     =>  $this->validator->getErrors(),
                    'message'   =>  lang('Language.invalid_inputs')
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }

        $ota_id = $this->request->getPost('ota_id');
        $country_code = $this->request->getPost('country_code');
        $mobile = $this->request->getPost('mobile');

        $device_token = $this->request->getPost('device_token') ? $this->request->getPost('device_token') : '';
        $device_type = $this->request->getPost('device_type');

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        // check user 
        $userdata = $UserModels->where("country_code", $country_code)->where("mobile", $mobile)->first();
        if (!empty($userdata)) {

            // $token = $this->service->getSignedAccessTokenForUser('user', $userdata['id']);
            $token = '1234567890';

            $updateuser = [
                // 'ota_id' => $ota_id,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'token' => $token
            ];

            $res = $db->table('tbl_user')->where('id', $userdata['id'])->update($updateuser);
            $MealBooking = $db->table('meals_booking')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $PackageBooking = $db->table('tbl_full_package_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $ZiyaratBooking = $db->table('tbl_package_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $SabeelBooking = $db->table('tbl_sabeel_booking')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $TransportBooking = $db->table('tbl_transport_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $VisaBooking = $db->table('tbl_visa_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();

            $userdata['token'] = $token;
            $bookingData['MealBooking'] = $MealBooking;
            $bookingData['PackageBooking'] = $PackageBooking;
            $bookingData['ZiyaratBooking'] = $ZiyaratBooking;
            $bookingData['SabeelBooking'] = $SabeelBooking;
            $bookingData['TransportBooking'] = $TransportBooking;
            $bookingData['VisaBooking'] = $VisaBooking;

            // Initialize bookingDetails as null
            $bookingDetails = null;

            // Iterate through each booking type and set bookingDetails if not empty
            foreach ($bookingData as $bookingType => $booking) {
                if (!empty($booking)) {
                    $bookingDetails = $booking;
                    break; // Stop the loop once a non-empty booking is found
                }
            }

            $userdata['bookingDetails'] = $bookingDetails;

            $response = [
                'status' => 'success',
                'status_code' => 200,
                'messages' => lang('Language.OTP Send successfully'),
                'data' => $userdata,
            ];
            return $this->respond($response);
        } else {
            $newuser = [
                'country_code' => $country_code,
                'mobile' => $mobile,
                'ota_id' => $ota_id,
                'created_by_id' => $ota_id,
                'user_role' => 'user',
                'created_by_role' => 'ota',
                'device_type' => $device_type,
                'device_token' => $device_token,
            ];

            $user_id = $UserModels->insert($newuser);
            // $token = $this->service->getSignedAccessTokenForUser('user', $user_id);

            $token = '1234567890';
            $updateuser = [
                'ota_id' => $ota_id,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'token' => $token
            ];

            $res = $UserModels->update($user_id, $updateuser);
            $user = $UserModels->where("country_code", $country_code)->where("mobile", $mobile)->first();

            // PUSH NOTIFICATION
            helper('notifications');
            $userinfo = $UserModels->where("mobile", $mobile)->first();

            $title = "Registration";
            $message = "Thanks for registering with Umrah Plus.";
            $fmc_ids = array($userinfo['device_token']);
            
            $notification = array(
                'title' => $title ,
                'message' => $message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                'date' => date('Y-m-d H:i'),
            );

            if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
            // EnD

            $response = [
                'status' => "success",
                'status_code' => 200,
                // 'otp' => $otp,
                'messages' => lang("Language.Users Create Successfully"),
                'data' => $user
            ];
            return $this->respond($response);
        }

        $response = [
            'status' => 'failed',
            'status_code' => 500,
            'messages' => lang('Language.User not found or Inactive')
        ];
        return $this->respond($response);
    }

    public function userRegtLoginwithMobileAndName()
    {
        $service        =  new Services();
        $service->cors();

        $UserModels = new UserModels();
        $OtaMoodel = new OtaMoodel();

        $db = \Config\Database::connect();

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'ota_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'country_code' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'mobile' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'name' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'device_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
        ];

        if(!$this->validate($rules)) {
            return $service->fail(
                [
                    'errors'     =>  $this->validator->getErrors(),
                    'message'   =>  lang('Language.invalid_inputs')
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }

        $ota_id = $this->request->getPost('ota_id');
        $country_code = $this->request->getPost('country_code');
        $mobile = $this->request->getPost('mobile');
        $name = $this->request->getPost('name');

        $device_token = $this->request->getPost('device_token') ? $this->request->getPost('device_token') : '';
        $device_type = $this->request->getPost('device_type');

        // check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

        // check user 
        $userdata = $UserModels->where("country_code", $country_code)->where("mobile", $mobile)->first();
        if (!empty($userdata)) {

            // $token = $this->service->getSignedAccessTokenForUser('user', $userdata['id']);
            $token = '1234567890';

            $updateuser = [
                'firstname' => $name,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'token' => $token
            ];

            $res = $db->table('tbl_user')->where('id', $userdata['id'])->update($updateuser);
            $MealBooking = $db->table('meals_booking')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $PackageBooking = $db->table('tbl_full_package_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $ZiyaratBooking = $db->table('tbl_package_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $SabeelBooking = $db->table('tbl_sabeel_booking')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $TransportBooking = $db->table('tbl_transport_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();
            $VisaBooking = $db->table('tbl_visa_enquiry')->where('user_id', $userdata['id'])->orderBy('id', 'desc')->get()->getRow();

            $userdata['token'] = $token;
            $bookingData['MealBooking'] = $MealBooking;
            $bookingData['PackageBooking'] = $PackageBooking;
            $bookingData['ZiyaratBooking'] = $ZiyaratBooking;
            $bookingData['SabeelBooking'] = $SabeelBooking;
            $bookingData['TransportBooking'] = $TransportBooking;
            $bookingData['VisaBooking'] = $VisaBooking;

            // Initialize bookingDetails as null
            $bookingDetails = null;

            // Iterate through each booking type and set bookingDetails if not empty
            foreach ($bookingData as $bookingType => $booking) {
                if (!empty($booking)) {
                    $bookingDetails = $booking;
                    break; // Stop the loop once a non-empty booking is found
                }
            }

            $userdata['bookingDetails'] = $bookingDetails;

            $response = [
                'status' => 'success',
                'status_code' => 200,
                'messages' => lang('Language.OTP Send successfully'),
                'data' => $userdata,
            ];
            return $this->respond($response);
        } else {
            $newuser = [
                'firstname' => $name,
                'country_code' => $country_code,
                'mobile' => $mobile,
                'ota_id' => $ota_id,
                'created_by_id' => $ota_id,
                'user_role' => 'user',
                'created_by_role' => 'ota',
                'device_type' => $device_type,
                'device_token' => $device_token,
            ];

            $user_id = $UserModels->insert($newuser);
            // $token = $this->service->getSignedAccessTokenForUser('user', $user_id);

            $token = '1234567890';
            $updateuser = [
                'ota_id' => $ota_id,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'token' => $token
            ];

            $res = $UserModels->update($user_id, $updateuser);
            $user = $UserModels->where("country_code", $country_code)->where("mobile", $mobile)->first();

            // PUSH NOTIFICATION
            helper('notifications');
            $userinfo = $UserModels->where("mobile", $mobile)->first();

            $title = "Registration";
            $message = "Thanks for registering with Umrah Plus.";
            $fmc_ids = array($userinfo['device_token']);
            
            $notification = array(
                'title' => $title ,
                'message' => $message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                'date' => date('Y-m-d H:i'),
            );

            if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
            // EnD

            $response = [
                'status' => "success",
                'status_code' => 200,
                // 'otp' => $otp,
                'messages' => lang("Language.Users Create Successfully"),
                'data' => $user
            ];
            return $this->respond($response);
        }

        $response = [
            'status' => 'failed',
            'status_code' => 500,
            'messages' => lang('Language.User not found or Inactive')
        ];
        return $this->respond($response);
    }

    // example  function
    // private function sendOTP(array $isCustomer)
    // {
    //     if (!empty($isCustomer) && is_array($isCustomer)) {
    //         helper('text');
    //         $OTP = random_string('numeric', 6);
    //         $data['otp'] = $OTP;
    //         $this->customer->allowCallbacks(false);

    //         if ( $this->customer->update($isCustomer['id'], $data) ) {

    //             $message['data']   =  '<div style="font-family: Quicksand, sans-serif;">Dear Customer,</div>';
    //             $message['data']  .=  '<br><div style="font-family: Quicksand, sans-serif;">Your PIN is: <b> '.$OTP.' </b></div>';
    //             $message['data']  .=  '<br><div style="font-family: Quicksand, sans-serif;">Please use this PIN to complete your login.</div><br><br>';
    //             $message['data']  .=  '<div style="font-family: Quicksand, sans-serif;">Kind regards,</div><br><div style="font-family: Quicksand, sans-serif;">Welzo Verification Team</div><br>';

    //             $message['email']  =  $this->service->encryption( $isCustomer['email'], 0);
    //             $message['support_email'] = 'contact@welzo.com';
    //             $msg_template = view('common/email_template', $message);
    //             $subject      = 'Login OTP';
    //             $to_email     =  $this->service->encryption( $isCustomer['email'], 0);

    //             return MailSender::sendMail($to_email, $subject, $msg_template , '', '', "Welzo", '');
    //             // return true;
    //         }
    //         return false;
    //     }
    //     return false;
    // }


    // // guide registration
    public function guideRegistration()
    {
        $email = \Config\Services::email();
        $GuideModel = new GuideModel();
        $GuideDocModel = new GuideDocModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $device_token = $this->request->getPost('device_token');
        $device_type = $this->request->getPost('device_type');

        // Email Validation
        $userdata = $GuideModel->where("email", $email)->first();
        if (!empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Already Exists')]);
            die();
        }

        if (isset($_FILES) && !empty($_FILES)) {
            $profile_pic = $this->request->getFile('profile_pic');
            $cover_pic = $this->request->getFile('cover_pic');

            if (!$profile_pic->isValid()) {
                throw new RuntimeException($profile_pic->getErrorString() . '(' . $profile_pic->getError() . ')');
            } else {
                $path1 = 'public/assets/uploads/guide/guide_profile_pic/';
                $newName1 = $profile_pic->getRandomName();
                $profile_pic->move($path1, $newName1);
            }

            if (!$cover_pic->isValid()) {
                throw new RuntimeException($cover_pic->getErrorString() . '(' . $cover_pic->getError() . ')');
            } else {
                $path2 = 'public/assets/uploads/guide/cover_pic/';
                $newName2 = $cover_pic->getRandomName();
                $cover_pic->move($path2, $newName2);
            }
        } else {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
            die();
        }

        $data = [
            "firstname" => $this->request->getPost("firstname"),
            "lastname" => $this->request->getPost("lastname"),
            "email" => $this->request->getPost("email"),
            "contact" => $this->request->getPost("contact"),
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "status" => 'active',
            "is_verify" => 'no',
            "language" => $this->request->getPost("languages"),
            "profile_pic" => $path1. $newName1,
            "cover_pic" => $path2. $newName2,
            // "govt_id_doc" => $this->request->getPost("govt_id_doc"),
            "dob" => $this->request->getPost("dob"),
            "nationality" => $this->request->getPost("nationality"),
            "education" => $this->request->getPost("education"),
            "experience" => $this->request->getPost("experience"),
            "home_address" => $this->request->getPost("home_address"),
            "city" => $this->request->getPost("city"),
            "country" => $this->request->getPost("country"),
            "about_us" => $this->request->getPost("about_us"),
            'device_type' => $device_type,
            'device_token' => $device_token,
        ];


        if ($GuideModel->insert($data)) {

            $guide_id = $GuideModel->insertID();
            // echo json_encode($guide_id);die();
            foreach ($this->request->getFileMultiple('image_array') as $file) {
                $guide_doc_path = 'public/assets/uploads/guide/document/';
                $new_name3 = $file->getRandomName();
                $doc_data = [
                    'guide_id' => $guide_id,
                    'status' => "active",
                    'guide_doc' => $guide_doc_path . $new_name3,
                ];
                $save = $GuideDocModel->insert($doc_data);
                $file->move($guide_doc_path, $new_name3);
            }

            // Send Email
            $usermail =  $email;
            $password =  $password;
            $firstname =  $data['firstname'];

            $regsterdata = array('email' => $usermail, 'password' => $password, 'username' => $firstname);
            $msg_template = view('emmail_templates/forgotpassword.php', $regsterdata);
            $subject      = 'Guide Email and Password';
            $to_email     =  $usermail;

            // PUSH NOTIFICATION
            helper('notifications');
            $userinfo = $GuideDocModel->where("email", $email)->first();
            $title = "Registration";
            $message = "Thanks for registering with Umrah Plus.";
            $fmc_ids = array($userinfo['device_token']);
            
            $notification = array(
                'title' => $title ,
                'message' => $message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                'date' => date('Y-m-d H:i'),
            );
            if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
            // EnD

            $abc =  MailSender::sendMail($to_email, $subject, $msg_template, '', '', "umarhaaddons", '');
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang("Language.Guide Created Successfully")
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 500,
                'messages' => lang("Language.Failed to Create")
            ];
        }
        return $this->respondCreated($response);
    }

    // ADD BY RIZ - 16 AUG 2022
    public function resendOTP()
    {
        $service   =  new Services();
        $email = \Config\Services::email();
        $UserModels = new UserModels();

        $email  =  $this->request->getVar('email');
        $userdata = $UserModels->where("email", $email)->first();

        if(!empty($userdata))
        {
            try {
                $otp = !empty( $userdata['otp'] ) ? $userdata['otp'] : $this->generateNumericOTP(6);
                $data = array('email' => $email, 'otp' => $otp);
                $msg_template = view('emmail_templates/userotp.php', $data);
                $subject      = 'Login OTP';
                $to_email     =  $email;
    
                $filename = "";
                $send     = sendEmail($to_email, $subject, $msg_template,$filename);
                return $service->success([
                    'message'       =>  Lang('Language.otp_send_success'),
                    'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.user_not_found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }
        } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.user_not_found'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    // TEST
    public function EmailTemplate()
    {
        $data = array('status' => 'accept', 'user_name' => 'Adam Bob');
        // $data = array('email' => 'riz@yopmail.com', 'password' => '1231546', 'username' => 'Ahmed');
        $msg_template = view('emmail_templates/guide_enquiry.php', $data);
        $abc =  MailSender::sendMail('rizwan.noorisys@gmail.com', 'Forgot Password', $msg_template , '', '', "umarhaaddons", '');
        return view('emmail_templates/guide_enquiry', $data);
    }

}

/* End of file UserRegistration.php */
/* Location: .//C/xampp/htdocs/amtik/app/Controllers/UserRegistration.php */