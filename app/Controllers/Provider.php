<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\AdminModel;
use App\Models\ActivitieModel;
use App\Models\UserModels;
use App\Models\PackageModels;
use App\Models\BookingModel;
use App\Models\ActivitieImgModel;
use App\Models\MovmentModels;
use App\Models\VehicleModels;
use App\Models\ImagePackageModels;
use App\Models\DayMappingModel;
use App\Models\VehicleMasterModel;
use App\Models\PaxMasaterModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

use Exception;

use Config\Services;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Provider extends ResourceController
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
			echo json_encode(['status' => 'failed', 'messages' => 'Access denied', 'status_code' => '401']);
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
        $ProviderModel = new ProviderModel();
        // $Employee = new Employee();
        // $customer = new CustomerModel();

        $key = $this->getKey();
        try {
            $decoded = JWT::decode($token, $key, array("HS256"));
            if ($decoded) {
                $id = $decoded->id;
                if ($role == "admin") {
                    $userdata = $AdminModel->where("token", $token)->where("id", $userid)->first();
                } elseif ($role == "provider") {
                    $userdata = $ProviderModel->where("token", $token)->where("id", $userid)->first();
                }
                //  elseif ($role == 2) {
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

    // update provider by Provider
    public function updateProviderByProvider()
    {
        $ProviderModel = new ProviderModel();
        $AdminModel = new AdminModel();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");

        // Email Validation
        $userdata = $ProviderModel->where('user_role', $logged_user_role)->where("id", $logged_user_id)->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
            die();
        }

        $data = [
            "firstname" => $this->request->getPost("firstname"),
            "lastname" => $this->request->getPost("lastname"),
            "company_name" => $this->request->getPost("company_name"),
            "mobile" => $this->request->getPost("mobile"),
            "gender" => $this->request->getPost("gender"),
            "city" => $this->request->getPost("city"),
            "state" => $this->request->getPost("state"),
            "country" => $this->request->getPost("country"),
            "zip_code" => $this->request->getPost("zip_code"),
            'supporter_no'=> $this->request->getPost("supporter_no"),
        ];
        if (!checkEmptyPost($data)) {
            if ($ProviderModel->update($logged_user_id, $data)) {
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang("Language.Provider Update Successfully")
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 500,
                    'messages' => lang("Language.Failed to update")
                ];
            }
        }
        return $this->respondCreated($response);
    }

    // active inactive package by provider
    public function activeInactivePackageByProvider()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $package_id = $this->request->getPost("package_id");
        $status = $this->request->getPost("status");



        //  Provider is avelable or not
        $userdata = $ProviderModel->where('user_role', $logged_user_role)->where("id", $logged_user_id)->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        // check package
        $packagedata = $PackageModels->where("id", $package_id)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        $made_by = $packagedata['provider_id'];

        if ($made_by === $logged_user_id) {
            if (!empty($packagedata)) {
                $status = ($status != "active") ? "inactive" : "active";
                $res = $PackageModels->update($package_id, ['status' => $status]);
                if ($res) {
                    $response = [
                        'status' => 'success',
                        'status_code' => 200,
                        'messages' => lang('Language.Package status changed successfully'),
                    ];
                } else {
                    $response = [
                        'status' => 'failed',
                        'status_code' => 500,
                        'messages' => lang('Language.Something wrong'),
                    ];
                }
            } else {
                $response = [
                    'status' => 'failed',
                    'status_code' => 500,
                    'messages' => lang('Language.Package Not Found')
                ];
            }
        } else {
            $response = [
                'status' => 'failed',
                'status_code' => 500,
                'messages' => lang('Language.Wrong Provider Id')
            ];
        }
        return $this->respondCreated($response);
    }

    // get detail of booking of user to perticular  package by provider
    public function getBookingDetailByProvider()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $BookingModel = new BookingModel();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $booking_id = $this->request->getPost('booking_id');

        //  Provider is avelable or not
        $bookingdata = $BookingModel->where('id', $booking_id)->where('provider_id', $logged_user_id)->first();
        if (empty($bookingdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Booking Not Found')]);
            die();
        }

        if ($logged_user_role == "provider") {
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_booking as l');
            $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name,CONCAT(d.firstname,' ',d.lastname) as user_name,pa.package_title as package_name,pax.name as pax_name,vec.name as vec_name ");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->join('tbl_user as d', 'd.id  = l.user_id');
            $builder1->join('tbl_package as pa', 'pa.id  = l.service_id');
            $builder1->join('tbl_pax_master as pax', 'pax.id  = l.no_of_pox');
            $builder1->join('tbl_vehicle_master as vec', 'vec.id  = l.cars');
            $builder1->where('l.provider_id', $logged_user_id);
            $builder1->where('l.id', $booking_id);
            $bookingdata = $builder1->get()->getRow();
            // $bookingdata = $BookingModel->slesect("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name,CONCAT(d.firstname,' ',d.lastname) as user_name,pa.package_title as package_name ")->where('id', $booking_id)->where('provider_id', $logged_user_id)->first();
            if (!empty($bookingdata)) {
                $response = [
                    'status' => 'success',
                    'status_code' => 200,
                    'messages' => lang("Language.Record Found"),
                    'info' => $bookingdata,
                ];
            } else {
                $response = [
                    'status' => 'failed',
                    'status_code' => 500,
                    'messages' => lang('Language.Wrong Booking Id')
                ];
            }
        } else {
            $response = [
                'status' => 'failed',
                'status_code' => 500,
                'messages' => lang('Language.Wrong Provider Id')
            ];
        }
        return $this->respondCreated($response);
    }

    // update booking status by  provider 
    public function acceptRejectBookingByProvider()
    {
        $ProviderModel = new ProviderModel();
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        $BookingModel = new BookingModel();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $booking_id = $this->request->getPost('booking_id');
        $package_id = $this->request->getPost('package_id');
        $user_id = $this->request->getPost('user_id');



        //  Provider is avelable or not
        $Providerdata = $ProviderModel->where('id', $logged_user_id)->where('status', 'active')->first();
        if (empty($Providerdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        //  Booking is avelable or not
        $bookingdata = $BookingModel->where('id', $booking_id)->where('provider_id', $logged_user_id)->first();
        if (empty($bookingdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Booking Not Found')]);
            die();
        }

        //  user is avelable or not
        $user_id = $bookingdata['user_id'];
        $user_data = $UserModels->where('id', $user_id)->where('status', 'active')->first();
        if (empty($user_data)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
            die();
        }


        //  package is avalable or not
        // $user_id = $bookingdata['user_id'];
        $packagedata = $PackageModels->where('id', $package_id)->where('status_by_admin', 'active')->where('status', 'active')->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        if ($logged_user_role == "provider") {
            $bookingdata = $BookingModel->where('id', $booking_id)->where('provider_id', $logged_user_id)->first();
            $data = [
                "action_by" => "provider",
                "action_by_id" => $logged_user_id,
                "action" => $this->request->getPost("action"),
            ];
            if ($BookingModel->update($booking_id, $data)) {
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang("Language.Booking status change")
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 500,
                    'messages' => lang("Language.Failed to update")
                ];
            }
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 500,
                'messages' => lang("Language.User Role Not Found")
            ];
        }
        return $this->respondCreated($response);
    }

    // get package detail for provider 
    public function getPackageDetailForProvider()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $package_id = $this->request->getPost('package_id');
        $MovmentModels = new MovmentModels();
        $ImagePackageModels = new ImagePackageModels();
        $VehicleModels = new VehicleModels();
        $DayMappingModel = new DayMappingModel();
        // $VehicleMasterModel = new VehicleMasterModel();
        // $PaxMasaterModel = new PaxMasaterModel();

        $active = "active";


        // check user
        $provider_data = $ProviderModel->where("id", $logged_user_id)->where("status", 'active')->first();
        if (empty($provider_data)) {
            echo json_encode(['status' => 'failed', 'status' => 404, 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        // Package Validation
        $packagedata = $PackageModels->where("id", $package_id)->where('provider_id', $logged_user_id)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        if ($logged_user_role == 'provider') {
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_package as l');
            $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->where('l.id', $package_id);
            $builder1->where('l.provider_id', $logged_user_id);
            $data = $builder1->get()->getResult();


            // fetching record of  Vechile  data
            $builder = $db->table('tbl_package_vehicle as pv');
            $builder->select('pv.*,pax.name as pax_name,vech.name as vech_name');
            $builder->join('tbl_pax_master as pax', 'pax.id  = pv.no_of_pox_id');
            $builder->join('tbl_vehicle_master as vech', 'vech.id  = pv.vehicle_id');
            $builder->where('pv.package_id', $package_id);
            $builder->where('pv.status', 'active');
            $Vehicle_data = $builder->get()->getResult();

           
            $image_data =  $ImagePackageModels->where("package_id", $package_id)->where('status', 'active')->findAll();
            $Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();
            foreach($Movment_data as $key => $value)
            {
                $inventatory_detail = $DayMappingModel->where('movement_id',$value['id'])->where('package_id',$value['package_id'])->findAll();

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
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 404,
                    'messages' => lang('Language.Package Details Not Found'),
                ];
            }
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.User Role Not Found'),
            ];
        }


        return $this->respondCreated($response);
    }

    // get activities detail for provider
    public function getActivitiesDetailForProvider()
    {
        $ProviderModel = new ProviderModel();
        $ActivitieModel = new ActivitieModel();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $Activitie_id = $this->request->getPost('activitie_id');
        $ActivitieImgModel = new ActivitieImgModel();


        // check provider
        $provider_data = $ProviderModel->where("id", $logged_user_id)->first();
        if (empty($provider_data)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        // Package Validation
        $Activitiedata = $ActivitieModel->where("id", $Activitie_id)->where('provider_id', $logged_user_id)->first();
        if (empty($Activitiedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Activitie Not Found')]);
            die();
        }

        if ($logged_user_role == 'provider') {
            $db = \Config\Database::connect();
            $builder1 = $db->table('tbl_activities as l');
            $builder1->select("l.*,CONCAT(c.firstname,' ',c.lastname) as provider_name");
            $builder1->join('tbl_provider as c', 'c.id  = l.provider_id');
            $builder1->where('l.id', $Activitie_id);
            $builder1->where('l.provider_id', $logged_user_id);
            $data = $builder1->get()->getResult();
            $image_data =  $ActivitieImgModel->where("activitie_id", $Activitie_id)->findAll();
            if (!empty($data)) {
                $response = [
                    'status' => "success",
                    'status_code' => 200,
                    'messages' => lang('Language.Activitie Details'),
                    'Package_data' => $data,
                    'Image_data' => $image_data
                ];
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 404,
                    'messages' => lang('Language.Activitie Details Not Found'),
                ];
            }
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 404,
                'messages' => lang('Language.User Role Not Found'),
            ];
        }


        return $this->respondCreated($response);
    }

    // active inactive activities by provider
    public function activeInactiveActivitiesByProvider()
    {
        $ProviderModel = new ProviderModel();
        $ActivitieModel = new ActivitieModel();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $activities_id = $this->request->getPost("activities_id");
        $status = $this->request->getPost("status");



        //  Provider is avelable or not
        $userdata = $ProviderModel->where('user_role', $logged_user_role)->where("id", $logged_user_id)->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        // check package
        $packagedata = $ActivitieModel->where("id", $activities_id)->where("provider_id", $logged_user_id)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Activitie Not Found')]);
            die();
        }

        $made_by = $packagedata['provider_id'];

        if ($made_by === $logged_user_id) {
            if (!empty($packagedata)) {
                $status = ($status != "active") ? "inactive" : "active";
                $res = $ActivitieModel->update($activities_id, ['status' => $status]);
                if ($res) {
                    $response = [
                        'status' => 'success',
                        'status_code' => 200,
                        'messages' => lang('Language.Activities status changed successfully'),
                    ];
                } else {
                    $response = [
                        'status' => 'failed',
                        'status_code' => 500,
                        'messages' => lang('Language.Something wrong'),
                    ];
                }
            } else {
                $response = [
                    'status' => 'failed',
                    'status_code' => 500,
                    'messages' => lang('Language.Activitie Not Found')
                ];
            }
        } else {
            $response = [
                'status' => 'failed',
                'status_code' => 500,
                'messages' => lang('Language.Wrong Provider Id')
            ];
        }
        return $this->respondCreated($response);
    }

    // service provided by provider 
    public function serviceProvidedByProvider()
    {
        $ProviderModel = new ProviderModel();
        $BookingModel = new BookingModel();
        $ActivitieModel = new ActivitieModel();
        $PackageModels = new PackageModels();
        $logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $booking_id = $this->request->getPost("booking_id");
        $service_type = $this->request->getPost("service_type");
        $service_id = $this->request->getPost("service_id");



        //  Provider is avelable or not
        $Providerdata = $ProviderModel->where('user_role', $logged_user_role)->where("id", $logged_user_id)->first();
        if (empty($Providerdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        if ($service_type == "package") {
            // check package
            $packagedata = $PackageModels->where("id", $service_id)->where("provider_id", $logged_user_id)->first();
            if (empty($packagedata)) {
                echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
                die();
            }
        } else {
            // check Activitie
            $activitiedata = $ActivitieModel->where("id", $service_id)->where("provider_id", $logged_user_id)->first();
            if (empty($activitiedata)) {
                echo json_encode(['status' => 'failed', 'messages' => lang('Language.Activitie Not Found')]);
                die();
            }
        }


        if ($logged_user_role == "provider") {
            $bookingdata = $BookingModel->where("id", $booking_id)->where("provider_id", $logged_user_id)->where("action", 'confirm')->where("payment_status", 'completed')->first();

            if (!empty($bookingdata)) {
                $data = [
                    'action_by_id' => $logged_user_id,
                    'action' => 'completed'
                ];
                if ($BookingModel->update($booking_id, $data)) {
                    $response = [
                        'status' => "success",
                        'status_code' => 200,
                        'messages' => lang("Language.Service Provided Successfully")
                    ];
                } else {
                    $response = [
                        'status' => "failed",
                        'status_code' => 500,
                        'messages' => lang("Language.Failed to Confirm Service")
                    ];
                }
            } else {
                $response = [
                    'status' => "failed",
                    'status_code' => 500,
                    'messages' => lang("Language.Booking Not Found")
                ];
            }
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 500,
                'messages' => lang("Language.User Role Not Found")
            ];
        }
        return $this->respondCreated($response);
    }
} // class end

/* End of file Provider.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Provider.php */