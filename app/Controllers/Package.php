<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\AdminModel;
use App\Models\ActivitieImgModel;
use App\Models\ActivitieModel;
use App\Models\PackageModels;
use App\Models\MovmentModels;
use App\Models\VehicleModels;
use App\Models\ImagePackageModels;
use App\Models\DayMappingModel;
use App\Models\PackageInquiryModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

use CodeIgniter\HTTP\ResponseInterface;

use Exception;

use Config\Services;
use RuntimeException;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Package extends ResourceController
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

        // $str = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
        // if ($str != 'accessDefine') {
        //     checkEmptyPost($_POST);
        // }

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
        $ProviderModel = new ProviderModel();
        $AdminModel = new AdminModel();
        // $workingpartner = new workingpartner();
        // $Employee = new Employee();
        // $customer = new CustomerModel();

        $key = $this->getKey();
        try {
            $decoded = JWT::decode($token, $key, array("HS256"));
            if ($decoded) {
                $id = $decoded->id;
                if ($role == "provider") {
                    $userdata = $ProviderModel->where("token", $token)->where("id", $userid)->first();
                }
                //  elseif ($role == 3) {
                // 	$userdata = $workingpartner->where("token", $token)->where("id", $userid)->first();
                // } elseif ($role == 2) {
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

    // add package by provider 
    public function addPackage()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $MovmentModels = new MovmentModels();
        $VehicleModels = new VehicleModels();
        $ImagePackageModels = new ImagePackageModels();
        $DayMappingModel = new DayMappingModel();


        $user_id = $this->request->getPost("logged_user_id");

        $pickup_loaction = $this->request->getPost("pickup_loaction");
        $drop_loaction = $this->request->getPost("drop_loaction");

        // Email Validation
        $userdata = $ProviderModel->where('user_role', "provider")->where("id", $user_id)->where("status", "active")->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'status_code' => 404, 'messages' => lang('Language.Provider Not Found')]);
            die();
        }
        if (isset($_FILES) && !empty($_FILES)) {
            $file = $this->request->getFile('main_img');
            if (!$file->isValid()) {
                throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
            } else {
                $path = 'public/assets/uploads/package/main_pic/';
                $newName = $file->getRandomName();
                $file->move($path, $newName);
            }
        } else {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
            die();
        }
        $data = [
            "provider_id" => $user_id,
            "package_title" => $this->request->getPost("package_title"),
            "city_loaction" => $this->request->getPost("city_loaction"),
            "ideal_for" => $this->request->getPost("ideal_for"),
            "main_img" =>  $path . $newName,
            "included" => $this->request->getPost("included"),
            "not_included" => $this->request->getPost("not_included"),
            
            "pickup_loaction" => (isset($pickup_loaction))?$pickup_loaction:'',
            "drop_loaction" => (isset($drop_loaction))?$drop_loaction:'',
            
            "status" => $this->request->getPost("status"),
            "accommodations" => $this->request->getPost("accommodations"),
            "accommodations_title" => $this->request->getPost("accommodations_title"),
            "accommodations_detail" => $this->request->getPost("accommodations_detail"),
            "return_policy" => $this->request->getPost("return_policy"),
            "type_of_package" => $this->request->getPost("type_of_package"),
            "language" => $this->request->getPost("language"),
            "package_details" => $this->request->getVar("package_details"),
            "ideal_for" => $this->request->getPost("ideal_for"),
            "package_duration" => (isset($_POST['package_duration'])) ? $this->request->getPost("package_duration") : "",
        ];


        if ($PackageModels->insert($data)) {

            $movment_json = $this->request->getPost("movment_json");
            $package_id = $PackageModels->insertID();
            // echo json_encode($package_id);die();
            // print_r($movment_json);die();
            $image_array = $this->request->getPost("image_array");
            $vehicle_json = $this->request->getPost("vehicle_json");


            $movements = json_decode($movment_json, TRUE);
            $vechiles = json_decode($vehicle_json, TRUE);

            // inserting movment  in tables
            foreach ($movements['inventatory'] as $key => $value) {
                $day_record = json_encode($value['inventatory_day']);
                $day = trim($day_record, '"');
                $add_movement = [
                    'package_id' => $package_id,
                    'day'=> $day
                ];
                $insert_Movment = $MovmentModels->insert($add_movement);
                $MovmentId = $MovmentModels->insertID();
                foreach ($value['inventatory_details'] as $k => $val) {
                    $time_record =  json_encode($val['time']);
                    $dec_record = json_encode($val['description']);
                    $time = trim($time_record, '"');
                    $description = trim($dec_record, '"');

                    $movment_data = [
                        'package_id' => $package_id,
                        'movement_id'=>$MovmentId,
                        'time' => $time,
                        'description' => $description,
                        'day'=>$day
                    ];  
                    $insert_Movment = $DayMappingModel->insert($movment_data);
                }
            }

            // inserting vechiles
            // foreach ($vechiles['vechile'] as $vec => $values) {
            foreach ($vechiles['vechiles_details'] as $kk => $vall) {
                $no_of_pox_rec =  json_encode($vall['no_of_pox']);
                $vehicle_type_rec = json_encode($vall['vehicle_type']);
                $rate_rec = json_encode($vall['rate']);
                $no_of_pox = trim($no_of_pox_rec, '"');
                $vehicle_type = trim($vehicle_type_rec, '"');
                $rate = trim($rate_rec, '"');

                $vechiles_data = [
                    'package_id' => $package_id,
                    'no_of_pox_id' => $no_of_pox,
                    'vehicle_id' => $vehicle_type,
                    'rate' => $rate,
                ];
                $insert_vechile = $VehicleModels->insert($vechiles_data);

                
            }
             // fetching record of  Vechile  data
            $db = \Config\Database::connect();
             $builder = $db->table('tbl_package_vehicle');
             $builder->select('MIN(rate) AS SmallestPrice');
             $builder->where('package_id', $package_id);
             $Smallestamount = $builder->get()->getResult();


             $SmallestPrices = $Smallestamount[0]->SmallestPrice;
            //  echo json_encode($SmallestPrices);die();

            
             // update package for Rate  
             $update_package = [
                "package_amount" => $SmallestPrices
             ];
             $PackageModels->update($package_id, $update_package);

            foreach ($this->request->getFileMultiple('image_array') as $file) {



                $package_pic_path = 'public/assets/uploads/package/package_pic/';
                $new_name = $file->getRandomName();
                $data = [
                    'package_id' => $package_id,
                    'status' => "active",
                    'package_imgs' => $package_pic_path . $new_name,
                ];
                $save = $ImagePackageModels->insert($data);
                $file->move($package_pic_path, $new_name);
            }
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang("Language.Package Create Successfully")
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


    // add activity by provider
    public function addActivities()
    {
        $ActivitieModel = new ActivitieModel();
        $ProviderModel = new ProviderModel();
        $ActivitieImgModel = new ActivitieImgModel();


        $user_id = $this->request->getPost("logged_user_id");

        $userdata = $ProviderModel->where('user_role', "provider")->where("id", $user_id)->where("status", "active")->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }
        if (isset($_FILES) && !empty($_FILES)) {
            $file = $this->request->getFile('main_img');
            if (!$file->isValid()) {
                throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
            } else {
                $path = 'public/assets/uploads/activitie/main_pic/';
                $newName = $file->getRandomName();
                $file->move($path, $newName);
            }
        } else {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
            die();
        }
        $data = [
            "provider_id" => $user_id,
            "activitie_title" => $this->request->getPost("activitie_title"),
            "city_loaction" => $this->request->getPost("city_loaction"),
            "ideal_for" => $this->request->getPost("ideal_for"),
            "main_img" =>  $path . $newName,
            "included" => $this->request->getPost("included"),
            "not_included" => $this->request->getPost("not_included"),
            "pickup_loaction" => $this->request->getPost("pickup_loaction"),
            "drop_loaction" => $this->request->getPost("drop_loaction"),
            // "status" => $this->request->getPost("status"),
            "accommodations" => $this->request->getPost("accommodations"),
            "accommodations_title" => $this->request->getPost("accommodations_title"),
            "accommodations_detail" => $this->request->getPost("accommodations_detail"),
            "return_policy" => $this->request->getPost("return_policy"),
            "type_of_activitie" => "both",
            "activitie_amount" => $this->request->getPost("activitie_amount"),
            "language" => $this->request->getPost("languages"),
            "reason" => $this->request->getVar("reason")
        ];


        if ($ActivitieModel->insert($data)) {

            $Activitie_id = $ActivitieModel->insertID();
            // $image_array = $this->request->getPost("image_array");

            foreach ($this->request->getFileMultiple('image_array') as $file) {

                $activitie_pic_path = 'public/assets/uploads/activitie/activitie_pic/';
                $new_name = $file->getRandomName();
                $data = [
                    'activitie_id' => $Activitie_id,
                    'status' => "active",
                    'activitie_img' => $activitie_pic_path . $new_name,
                ];
                $save = $ActivitieImgModel->insert($data);
                $file->move($activitie_pic_path, $new_name);
            }
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang("Language.Activitie Create Successfully")
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

    public function storeMultipleFile()
    {
        $ImagePackageModels = new ImagePackageModels();

        helper(['form', 'url']);

        $db      = \Config\Database::connect();
        $builder = $db->table('file');

        $msg = 'Please select a valid files';


        foreach ($this->request->getFileMultiple('file') as $file) {

            $file->move(WRITEPATH . 'public/assets/uploads/package/package_pic/');

            $path = 'public/assets/uploads/package/package_pic/';
            $new_name = $file->getRandomName();
            $data = [
                'package_imgs' => $path . $new_name
                // 'type'  => $file->getClientMimeType()
            ];
            $save = $ImagePackageModels->insert($data);
            echo   'Files has been uploaded';
        }
        echo   'Please select a valid files';


        //    return redirect()->to( base_url('public/index.php/form/multipleImage') )->with('msg', $msg);

    }

    // update pckage by provider 
    public function updatePackageByProvider()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $MovmentModels = new MovmentModels();
        $VehicleModels = new VehicleModels();
        $ImagePackageModels = new ImagePackageModels();

        $user_id = $this->request->getPost("logged_user_id");
        $package_id = $this->request->getPost("package_id");

        // Email Validation
        $userdata = $ProviderModel->where('user_role', "provider")->where("id", $user_id)->where("status", "active")->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
            die();
        }

        // check package
        $packagedata = $PackageModels->where("id", $package_id)->where("provider_id", $user_id)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

        if (isset($_FILES) && !empty($_FILES)) {
            $file = $this->request->getFile('main_img');
            if (!$file->isValid()) {
                throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
            } else {
                $path = 'public/assets/uploads/package/main_pic/';
                $newName = $file->getRandomName();
                $file->move($path, $newName);
            }
        } else {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
            die();
        }
        $data = [
            "package_title" => $this->request->getPost("package_title"),
            "city_loaction" => $this->request->getPost("city_loaction"),
            "ideal_for" => $this->request->getPost("ideal_for"),
            "main_img" =>  $path . $newName,
            "included" => $this->request->getPost("included"),
            "not_included" => $this->request->getPost("not_included"),
            "pickup_loaction" => $this->request->getPost("pickup_loaction"),
            "drop_loaction" => $this->request->getPost("drop_loaction"),
            "accommodations" => $this->request->getPost("accommodations"),
            "status" => "active",
            "accommodations_title" => $this->request->getPost("accommodations_title"),
            "accommodations_detail" => $this->request->getPost("accommodations_detail"),
            "return_policy" => $this->request->getPost("return_policy"),
            "type_of_package" => $this->request->getPost("type_of_package"),
            "package_amount" => $this->request->getPost("package_amount"),
        ];

        if ($PackageModels->update($package_id, $data)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang("Language.Package Updated Successfully")
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 500,
                'messages' => lang("Language.Failed to Update")
            ];
        }
        return $this->respondCreated($response);
    }

    // update activity by provider 
    public function updateActivitiesByProvider()
    {
        $ActivitieModel = new ActivitieModel();
        $ProviderModel = new ProviderModel();


        $user_id = $this->request->getPost("logged_user_id");
        $Activitie_id = $this->request->getPost("activitie_id");


        $userdata = $ProviderModel->where('user_role', "provider")->where("id", $user_id)->where("status", "active")->first();
        if (empty($userdata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
            die();
        }

        $activitidata = $ActivitieModel->where('id', $Activitie_id)->where("provider_id", $user_id)->first();
        if (empty($activitidata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Activitie Not Found')]);
            die();
        }
        if (isset($_FILES) && !empty($_FILES)) {
            $file = $this->request->getFile('main_img');
            if (!$file->isValid()) {
                throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
            } else {
                $path = 'public/assets/uploads/activitie/main_pic/';
                $newName = $file->getRandomName();
                $file->move($path, $newName);
            }
        } else {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
            die();
        }
        $data = [
            "activitie_title" => $this->request->getPost("activitie_title"),
            "city_loaction" => $this->request->getPost("city_loaction"),
            "ideal_for" => $this->request->getPost("ideal_for"),
            "main_img" =>  $path . $newName,
            "included" => $this->request->getPost("included"),
            "not_included" => $this->request->getPost("not_included"),
            "pickup_loaction" => $this->request->getPost("pickup_loaction"),
            "drop_loaction" => $this->request->getPost("drop_loaction"),
            "accommodations" => $this->request->getPost("accommodations"),
            "accommodations_title" => $this->request->getPost("accommodations_title"),
            "accommodations_detail" => $this->request->getPost("accommodations_detail"),
            "return_policy" => $this->request->getPost("return_policy"),
            "activitie_amount" => $this->request->getPost("activitie_amount"),
            "reason" => $this->request->getVar("reason")
        ];


        if ($ActivitieModel->update($Activitie_id, $data)) {
            $response = [
                'status' => "success",
                'status_code' => 200,
                'messages' => lang("Language.Activitie Updated Successfully")
            ];
        } else {
            $response = [
                'status' => "failed",
                'status_code' => 500,
                'messages' => lang("Language.Failed to Updated")
            ];
        }
        return $this->respondCreated($response);
    }

    // NEW FRO PACKAGE ENQUIRY - 02 SPE 2022
    public function packageEnquirySend()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $package_enqiry   = new PackageInquiryModel();
        $service->cors();

        $provider_id    =  $this->request->getVar('provider_id');
        $ota_id         =  $this->request->getVar('ota_id');
        $package_id     =  $this->request->getVar('package_id');
        $from_date      =  $this->request->getVar('from_date');
        $no_of_pax      =  $this->request->getVar('no_of_pax');
        $package_price  =  $this->request->getVar('package_price');
        $total_amount   =  $this->request->getVar('total_amount');
        $full_name      =  $this->request->getVar('full_name');
        $email_address  =  $this->request->getVar('email_address');
        $country        =  $this->request->getVar('country');
        $mobile         =  $this->request->getVar('mobile');

        $rules = [
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'logged_user_role' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'provider_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'package_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'from_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'ota_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'no_of_pax' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'total_amount' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'full_name' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'email_address' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'country' => [
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

        // $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if(true)
        {
            try 
            {
                $data = array(
                    'user_id'         => $_POST['logged_user_id'],
                    'provider_id'     => $provider_id,
                    'ota_id'          => $ota_id,
                    'package_id'      => $package_id,
                    'from_date'       => $from_date,
                    'no_of_pax'       => $no_of_pax, 
                    'package_amount'  => $package_price,
                    'total_amount'    => $total_amount,
                    'full_name'       => $full_name,
                    'email_address'   => $email_address,
                    'country'         => $country,
                    'mobile'          => $mobile,
                    'created_date'    => date('Y-m-d H:i:s')
                );

                if($package_enqiry->insert($data)) 
                {
                    // PUSH NOTIFICATION
                    helper('notifications');
                    $db = db_connect();
                    $userinfo = $db->table('tbl_user')
                        ->select('*')
                        ->where('id', $_POST['logged_user_id'])
                        ->get()->getRow();

                        $providerinfo = $db->table('tbl_provider')
                         ->select('*')
                         ->where('id', $provider_id)
                         ->get()->getRow();
                    
                        $title = "Package Inquiry";
                        $message = "Inquiry received from a ".$full_name." for a Package";
                        $fmc_ids = array($providerinfo->device_token);
                        $notification = array(
                            'title' => $title ,
                            'message' => $message,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                            'date' => date('Y-m-d H:i'),
                        );
                        if($userinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }

                    $title = "Package Inquiry";
                    $message = "Your Inquiry has been sent. Thank you.";

                    $fmc_ids = array($userinfo['device_token'], $providerinfo['device_token']);
                    
                    $notification = array(
                        'title' => $title ,
                        'message' => $message,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                        'date' => date('Y-m-d H:i'),
                    );
                    if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }
                    // EnD

                    return $service->success([
                            'message'       =>  Lang('Language.add_success'),
                            'data'          =>  ""
                        ],
                        ResponseInterface::HTTP_CREATED,
                        $this->response
                    );
                } else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.add_failed'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.add_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.auth_failure'),
                ],
                ResponseInterface::HTTP_UNAUTHORIZED,
                $this->response
            );
        }

    }

    public function packageEnquiryView()
    {
        $service   =  new Services();
        $package_enqiry   = new PackageInquiryModel();
        $service->cors();

        $enquiry_id   =  $this->request->getVar('enquiry_id');

        try {
            $db = db_connect();
            $info = $db->table('tbl_package_enquiry as e')
                ->join('tbl_provider as p','p.id = e.provider_id')
                ->join('tbl_package as pk','pk.id = e.package_id')
                ->join('tbl_user as u','u.id = e.user_id')
                ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, CONCAT(u.firstname,' ',u.lastname) as user_name, pk.package_title as package_name")
                ->where('e.status','1')
                ->where('p.status','active')
                ->where('e.id',$enquiry_id)
                ->get()->getRow();

         if(!empty($info))
         {
            return $service->success([
                'message'       =>  Lang('Language.details_success'),
                'data'          =>  $info
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
         } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.enquiry_not_found'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
         }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.enquiry_not_found'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function packageEnquiryList()
    {
        $service   =  new Services();
        $package_enqiry   = new PackageInquiryModel();
        $service->cors();

        $user_role        =  $this->request->getVar('logged_user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $pageNo           =  $this->request->getVar('pageNo');

        try{
                $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
                $offset        = ( $currentPage - 1 ) * PER_PAGE;
                $limit         =  PER_PAGE;

                $whereCondition = '';

                if($user_role == 'admin'){ $whereCondition .= "e.status = 'active'"; }

                elseif($user_role == 'provider'){ $whereCondition .= "e.provider_id = ".$logged_user_id." AND e.status = 'active'"; }

                elseif($user_role == 'user'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = 'active'"; }

                elseif($user_role == 'ota'){ $whereCondition .= "e.ota_id = ".$logged_user_id." AND e.status = 'active'"; }

                $db = db_connect();
                $data = $db->table('tbl_package_enquiry as e')
                    ->join('tbl_provider as p','p.id = e.provider_id')
                    ->join('tbl_package as pk','pk.id = e.package_id')
                    ->join('tbl_user as u','u.id = e.user_id')
                    ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, CONCAT(u.firstname,' ',u.lastname) as user_name, pk.package_title as package_name")
                    ->where($whereCondition)
                    ->orderBy('e.id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()->getResult();
                    
                $total = count($data);

                return $service->success(
                    [
                        'message'       =>  Lang('Language.list_success'),
                        'data'          =>  [
                            'total'             =>  $total,
                            'enquiries'         =>  $data,
                        ]
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.fetch_list'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }
    }

    // 30 SEP 2022 - RIZ
    public function packageDelete()
    {
        $service   =  new Services();
        $package     = new PackageModels();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        $package_id       =  $this->request->getVar('package_id');
        $logged_user_id       =  $this->request->getVar('logged_user_id');
        $logged_user_role       =  $this->request->getVar('logged_user_role');
        $token       =  $this->request->getVar('authorization');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'logged_user_role' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'package_id' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $logged_user_role);

        if($checkToken)
        {
            try {
                    $provider_data = $ProviderModel->where("id", $logged_user_id)->where("status", 'active')->first();
                    if (empty($provider_data)) {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.Provider Not Found'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

                 $isExist = $package->where(['id'=> $package_id])->first();
                 if(!empty($isExist))
                 {
                    $update = $package->update($package_id, ['status' => 'inactive']);
                    return $service->success([
                        'message'       =>  Lang('Language.delete_success'),
                        'data'          =>  ''
                        ],
                        ResponseInterface::HTTP_OK,
                        $this->response
                    );
                 } else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Package Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                 }

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.delete_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.auth_failure'),
                ],
                ResponseInterface::HTTP_UNAUTHORIZED,
                $this->response
            );
        }
    }

    // Accept / Reject Package requests
    public function acceptRejectRequest()
    {
       echo "YES";
    }

    // make package featured/unfeatured by Javeriya
    public function makePackageFaetured()
    {
        $service   =  new Services();
        $package     = new PackageModels();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        $package_id       =  $this->request->getVar('package_id');
        $logged_user_id       =  $this->request->getVar('logged_user_id');
        $logged_user_role       =  $this->request->getVar('logged_user_role');
        $token       =  $this->request->getVar('authorization');
        $is_featured       =  $this->request->getVar('is_featured');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'logged_user_role' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'package_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'is_featured' => [
                'rules'         =>  'required|in_list[yes,no]',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', ['yes,no']),
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

        $checkToken = $service->getAccessForSignedUser($token, $logged_user_role);

        if($checkToken)
        {
            try {
                    $provider_data = $ProviderModel->where("id", $logged_user_id)->where("status", 'active')->first();
                    if (empty($provider_data) && $logged_user_role == 'provider') {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.Provider Not Found'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

                 $isExist = $package->where(['id'=> $package_id])->first();
                 if(!empty($isExist))
                 {
                    $update = $package->update($package_id, ['is_featured' => $is_featured]);
                    return $service->success([
                        'message'       =>  Lang('Language.Package Updated Successfully'),
                        'data'          =>  ''
                        ],
                        ResponseInterface::HTTP_OK,
                        $this->response
                    );
                 } else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Package Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                 }

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.update_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.auth_failure'),
                ],
                ResponseInterface::HTTP_UNAUTHORIZED,
                $this->response
            );
        }
    }

} // class end

/* End of file Package.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Package.php */