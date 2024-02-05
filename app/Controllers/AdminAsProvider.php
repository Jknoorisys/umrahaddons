<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\PackageModels;
use App\Models\MovmentModels;
use App\Models\ImagePackageModels;
use App\Models\VehicleModels;
use App\Models\DayMappingModel;
use App\Models\MealsMenuModel;
use App\Models\MealsModel;
use App\Models\SabeelModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\LandingPageBanners;
use \Firebase\JWT\JWT;

use Exception;

use Config\Services;
use RuntimeException;

use CodeIgniter\HTTP\ResponseInterface;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class AdminAsProvider extends BaseController
{
    private $user_id = null;
	private $user_role = null;
	private $token = null;
	private $service;

	public function __construct()
	{
		$this->service  = new Services();
		$this->service->cors();

		helper('auth');
		helper('notifications');

		$lang = (isset($_POST["language"]) && !empty($_POST["language"])) ? $_POST["language"] : '';
		if (!empty($lang)) {
			$language = \Config\Services::language();
			$language->setLocale($lang);
		} else {
			echo json_encode(['status' => 403, 'messages' => 'Language is Required']);
			die();
		}

		$db = \Config\Database::connect();
		// Check Authentication
        
		$this->token = $token = (isset($_POST["authorization"]) && !empty($_POST["authorization"])) ? $_POST["authorization"] : '';
		$this->user_id = $user_id = (isset($_POST["logged_user_id"]) && !empty($_POST["logged_user_id"])) ? $_POST["logged_user_id"] : '';
		$this->user_role = $user_role = (isset($_POST["logged_user_role"]) && !empty($_POST["logged_user_role"])) ? $_POST["logged_user_role"] : '';

        if (empty($token)) {
			echo json_encode(['status' => 403, 'messages' => 'Authorization Token is Required']);
			die();
		} 

        if (empty($user_id)) {
			echo json_encode(['status' => 403, 'messages' => 'User ID is Required']);
			die();
		} 

        if (empty($user_role)) {
			echo json_encode(['status' => 403, 'messages' => 'User Role is Required']);
			die();
		} 

		if (!$this->service->getAccessForSignedUser($token, $user_role)) {
			echo json_encode(['status' => 'failed', 'messages' => 'Access denied', 'status_code' => '401']);
			die();
		}

		$timezone = "Asia/Kolkata";
		date_default_timezone_set($timezone);
	}

	private $user_excluded_keys = array("password", "token");

	use ResponseTrait;

	// add package by Admin - by Javeriya Kauser
    public function addPackage()
    {
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $MovmentModels = new MovmentModels();
        $VehicleModels = new VehicleModels();
        $ImagePackageModels = new ImagePackageModels();
        $DayMappingModel = new DayMappingModel();
        $user_id = $this->request->getPost("provider_id");
        $service   =  new Services();
        $service->cors();

        $pickup_loaction = $this->request->getPost("pickup_loaction");
        $drop_loaction = $this->request->getPost("drop_loaction");
        $rules = [
            'package_type' => [
                'rules'         =>  'required|in_list[individual,group]',
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

        $package_type = $this->request->getPost("package_type");
        if ($package_type == "individual") {
            $rules = [
                'individual_price' => [
                    'rules'         =>  'required',
                    'errors'        => [
                        'required'      =>  Lang('Language.required'),
                    ]
                ],
            ];
        } elseif ($package_type == "group") {
            $rules = [
                'vehicle_json' => [
                    'rules'         =>  'required',
                    'errors'        => [
                        'required'      =>  Lang('Language.required'),
                    ]
                ],
            ];
        }

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
            "package_type" => $package_type,
            "individual_price" => $this->request->getPost("individual_price") ? $this->request->getPost("individual_price") : "",
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
            "ziyarat_points" => $this->request->getPost("ziyarat_points") ? $this->request->getPost("ziyarat_points") : "",
        ];


        if ($PackageModels->insert($data)) {

            $movment_json = $this->request->getPost("movment_json");
            $package_id = $PackageModels->insertID();
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
            if ($vechiles && $vehicle_json && $package_type == "group") {
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
            }
             // fetching record of  Vechile  data
            $db = \Config\Database::connect();
            $builder = $db->table('tbl_package_vehicle');
            $builder->select('MIN(rate) AS SmallestPrice');
            $builder->where('package_id', $package_id);
            $Smallestamount = $builder->get()->getResult();


            $SmallestPrices = $Smallestamount[0]->SmallestPrice;
            
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

    // update package by Admin - by Javeriya Kauser
    public function updatePackage()
    {
        $service   =  new Services();
        $ProviderModel = new ProviderModel();
        $PackageModels = new PackageModels();
        $MovmentModels = new MovmentModels();
        $VehicleModels = new VehicleModels();
        $ImagePackageModels = new ImagePackageModels();
        $DayMappingModel = new DayMappingModel();

        $rules = [
            'package_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => lang('Language.required'),
                ],
            ],
            'package_type' => [
                'rules'         =>  'required|in_list[individual,group]',
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
        
        $package_type = $this->request->getPost("package_type");
        if ($package_type == "individual") {
            $rules = [
                'individual_price' => [
                    'rules'         =>  'required',
                    'errors'        => [
                        'required'      =>  Lang('Language.required'),
                    ]
                ],
            ];
        } elseif ($package_type == "group") {
            $rules = [
                'vehicle_json' => [
                    'rules'         =>  'required',
                    'errors'        => [
                        'required'      =>  Lang('Language.required'),
                    ]
                ],
            ];
        }

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

        $image_array = $this->request->getFileMultiple('image_array');
        $movements = json_decode($this->request->getPost("movement_json"), TRUE);
        $vechiles = json_decode($this->request->getPost("vehicle_json"), TRUE);

        $package_id = $this->request->getPost("package_id");
        $individual_price = $this->request->getPost("individual_price");
        $package_title = $this->request->getPost("package_title");
        $package_details = $this->request->getPost("package_details");
        $city_loaction = $this->request->getPost("city_loaction");
        $ideal_for = $this->request->getPost("ideal_for");
        $included = $this->request->getPost("included");
        $not_included = $this->request->getPost("not_included");
        $pickup_loaction = $this->request->getPost("pickup_loaction");
        $drop_loaction = $this->request->getPost("drop_loaction");
        $accommodations = $this->request->getPost("accommodations");
        $accommodations_title = $this->request->getPost("accommodations_title");
        $accommodations_detail = $this->request->getPost("accommodations_detail");
        $return_policy = $this->request->getPost("return_policy");
        $type_of_package = $this->request->getPost("type_of_package");
        $reason = $this->request->getPost("reason");
        $language = $this->request->getPost("language");
        $ziyarat_points = $this->request->getPost("ziyarat_points");

        $db = db_connect();
        $package = $db->table('tbl_package')->where('id', $package_id)->get()->getRow();

        if(!empty($package))
        {
            $file = $this->request->getFile('main_img');
            if ($file) {
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                } else {
                    $path = 'public/assets/uploads/package/main_pic/';
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $url = $path . $newName;
                }
            }else{
                $url = $package->main_img;
            }
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

        $price = $package_type == "individual" ? ($individual_price ? $individual_price : $package->individual_price) : '';
        
        $data = [
            "package_type" => $package_type ? $package_type : $package->package_type,
            "individual_price" => $price,
            "package_title" => $package_title ? $package_title : $package->package_title,
            "package_details" => $package_details ? $package_details : $package->package_details,
            "city_loaction" => $city_loaction ? $city_loaction : $package->city_loaction,
            "ideal_for" => $ideal_for ? $ideal_for : $package->ideal_for,
            "main_img" =>  $url,
            "included" => $included ? $included : $package->included,
            "not_included" => $not_included ? $not_included : $package->not_included,
            "pickup_loaction" => $pickup_loaction ? $pickup_loaction : $package->pickup_loaction,
            "drop_loaction" => $drop_loaction ? $drop_loaction : $package->drop_loaction,
            "accommodations" => $accommodations ? $accommodations : $package->accommodations,
            "accommodations_title" => $accommodations_title ? $accommodations_title : $package->accommodations_title,
            "accommodations_detail" => $accommodations_detail ? $accommodations_detail : $package->accommodations_detail,
            "return_policy" => $return_policy ? $return_policy : $package->return_policy,
            "type_of_package" => $type_of_package ? $type_of_package : $package->type_of_package,
            "reason" => $reason ? $reason : $package->reason,
            "language" => $language ? $language : $package->language,
            "ziyarat_points" => $ziyarat_points ? $ziyarat_points : $package->ziyarat_points,
            "updated_date" => date('Y-m-d H:i:s')
        ];

        $package_update = $db->table('tbl_package')->where('id', $package_id)->update($data);
        if ($package_update) {

            if ($vechiles && $package_type == "group") {
                $vechile_ids = [];
                foreach ($vechiles as $vechile) {
                    if ($vechile['id'] != "0") {
                        $vechile_ids[] = $vechile['id'];

                        $vechile_data = [
                            'no_of_pox_id' => $vechile['no_of_pox_id'],
                            'package_id'         => $package_id,
                            'rate'         => $vechile['rate'],
                            'vehicle_id' => $vechile['vehicle_id'],
                            'updated_date' => date('Y-m-d H:i:s')
                        ];

                        $db->table('tbl_package_vehicle')->where('id', $vechile['id'])->update($vechile_data);
                    }elseif ($vechile['id'] == 0) {
                        $vechile_data = [
                            'no_of_pox_id' => $vechile['no_of_pox_id'],
                            'package_id'         => $package_id,
                            'rate'         => $vechile['rate'],
                            'vehicle_id' => $vechile['vehicle_id'],
                            'created_date' => date('Y-m-d H:i:s'),
                            'updated_date' => date('Y-m-d H:i:s')
                        ];

                       $insert = $db->table('tbl_package_vehicle')->insert($vechile_data);
                       $insertID = $db->insertID();
                       $vechile_ids[] = $insertID;
                    }
                }

                if ($vechile_ids) {
                    $remove = $db->table('tbl_package_vehicle')->where('package_id', $package_id)->whereNotIn('id', $vechile_ids)->delete();
                }

                $db = \Config\Database::connect();
                $builder = $db->table('tbl_package_vehicle');
                $builder->select('MIN(rate) AS SmallestPrice');
                $builder->where('package_id', $package_id);
                $Smallestamount = $builder->get()->getResult();
    
    
                $SmallestPrices = $Smallestamount[0]->SmallestPrice;
                
                 // update package for Rate  
                 $update_package = [
                    "package_amount" => $SmallestPrices
                 ];
                 $PackageModels->update($package_id, $update_package);
            } elseif($package_type == "individual") {
                $update_package = [
                    "package_amount" => $individual_price
                ];
                $PackageModels->update($package_id, $update_package);
                $remove = $db->table('tbl_package_vehicle')->where('package_id', $package_id)->delete();
            }

            if ($movements) {
                $movement_ids = [];
                foreach ($movements as $movement) {
                    if ($movement['id'] != "0") {
                        $movement_ids[] = $movement['id'];
                        $movement_id = $movement['id'];

                        $movement_data = [
                            'day' => $movement['day'],
                            'package_id'         => $package_id,
                            'updated_date' => date('Y-m-d H:i:s')
                        ];

                        $db->table('tbl_package_movment')->where('id', $movement['id'])->update($movement_data);
                    }elseif ($movement['id'] == 0) {
                        $movement_data = [
                            'day' => $movement['day'],
                            'package_id'         => $package_id,
                            'created_date' => date('Y-m-d H:i:s'),
                            'updated_date' => date('Y-m-d H:i:s')
                        ];

                       $insert = $db->table('tbl_package_movment')->insert($movement_data);
                       $insertID = $db->insertID();
                       $movement_id = $insertID;
                       $movement_ids[] = $insertID;
                    }

                    $inventories = $movement['inventatory_details'];
                    $inventory_ids = [];
                    foreach ($inventories as $inventory) {
                        if ($inventory['id'] != "0") {
                            $inventory_ids[] = $inventory['id'];                            
                            $inventory_data = [
                                'movement_id' => $movement_id,
                                'package_id'         => $package_id,
                                'day' => $movement['day'],
                                'time' => $inventory['time'],
                                'description' => $inventory['description'],
                                'updated_date' => date('Y-m-d H:i:s')
                            ];

                            $db->table('tbl_package_day_mapping')->where('id', $inventory['id'])->update($inventory_data);
                        }elseif ($inventory['id'] == 0) {
                            $inventory_data = [
                                'movement_id' => $movement_id,
                                'package_id'         => $package_id,
                                'day' => $movement['day'],
                                'time' => $inventory['time'],
                                'description' => $inventory['description'],
                                'created_date' => date('Y-m-d H:i:s'),
                                'updated_date' => date('Y-m-d H:i:s')
                            ];

                            $insert = $db->table('tbl_package_day_mapping')->insert($inventory_data);
                            $insertID1 = $db->insertID();
                            $inventory_ids[] = $insertID1;
                        }
                    }

                    if ($inventory_ids) {
                        $remove = $db->table('tbl_package_day_mapping')->where('movement_id', $movement_id)->whereNotIn('id', $inventory_ids)->delete();
                    }
                }

                if ($movement_ids) {
                    $remove = $db->table('tbl_package_movment')->where('package_id', $package_id)->whereNotIn('id', $movement_ids)->delete();
                }
            }

            if ($image_array) {
                $imgs = $db->table('tbl_package_image')->where('package_id', $package_id)->delete();
                foreach ($this->request->getFileMultiple('image_array') as $file) {
                    $package_pic_path = 'public/assets/uploads/package/package_pic/';
                    $new_name = $file->getRandomName();
                    $data = [
                        'package_id' => $package_id,
                        'package_imgs' => $package_pic_path . $new_name,
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                    $save = $ImagePackageModels->insert($data);
                    $file->move($package_pic_path, $new_name);
                }
            }

            $packageDetails = $db->table('tbl_package')->where('id', $package_id)->get()->getRowArray();
            $packageDetails['movements'] = $db->table('tbl_package_movment')->where('package_id', $package_id)->get()->getResult();
            $packageDetails['vehicles'] = $db->table('tbl_package_vehicle')->where('package_id', $package_id)->get()->getResult();
            $packageDetails['images'] = $db->table('tbl_package_image')->where('package_id', $package_id)->get()->getResult();

            return $service->success([
                'message'       =>  Lang('Language.update_success'),
                'data'          =>  ""
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
        } else {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.update_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }


        if ($PackageModels->insert($data)) {

            $movment_json = $this->request->getPost("movment_json");
            $package_id = $PackageModels->insertID();
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

    // Delete package by Admin - by Javeriya Kauser
    public function packageDelete()
    {
        $service   =  new Services();
        $package     = new PackageModels();
        $ProviderModel     = new ProviderModel();
        $bannerModel    =  new LandingPageBanners();
        $service->cors();
        $package_id       =  $this->request->getVar('package_id');
        $provider_id       =  $this->request->getVar('provider_id');
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
            'provider_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
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
                    $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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
                    if ($update) {
                        $db = db_connect();
                        $delete = $db->table('tbl_landing_page_banners')
                            ->where('package_id', $package_id)
                            ->set('status', 'deleted')
                            ->update();
                    }
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

    // Add Sabeel by Admin - by Javeriya Kauser
    public function addSabeel()
    {
        $service        =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('authorization');
        $provider_id       =  $this->request->getVar('provider_id');
        $user_role         =  $this->request->getVar('logged_user_role');

        $name              =  $this->request->getVar('name');
        $description       =  $this->request->getVar('description');
        $price             =  $this->request->getVar('price');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'provider_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
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
            'name' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'description' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'price' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try 
            {
                $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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
                
                $validated = $this->validate([
                    'file' => [
                        'uploaded[photo]',
                        'mime_in[photo,image/jpg,image/jpeg,image/png]',
                        'max_size[photo,5120]',
                    ],
                ]);

                if($validated)
                {   
                    $file_path = 'public/assets/uploads/sabeel/';
                    $photo  =  $this->request->getFile('photo');
                    $tempname  = $photo->getRandomName();
                    $photo->move($file_path, $tempname);
                    $photo_url = $file_path . $tempname;

                    $data = array(
                        'provider_id'    =>    $provider_id,
                        'name'           =>    $name,
                        'description'    =>    $description,
                        'price'          =>    $price,
                        'photo'          =>    $photo_url,
                        'created_date'   => date('Y-m-d H:i:s'),
                    );
                    if($sabeel->insert($data)) 
                    {
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
                }
                else {
                    return $service->fail(
                        [
                            'errors'     =>  $this->validator->getErrors(),
                            'message'   =>  Lang('Language.upload_failed'),
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

    // Update Sabeel by Admin - by Javeriya Kauser
    public function updateSabeel()
    {
        $service        =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('authorization');
        $provider_id       =  $this->request->getVar('provider_id');

        $user_role         =  $this->request->getVar('logged_user_role');

        $name              =  $this->request->getVar('name');
        $description       =  $this->request->getVar('description');
        $price             =  $this->request->getVar('price');
        $sabeel_id         =  $this->request->getVar('sabeel_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'provider_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'sabeel_id' => [
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
            'name' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'description' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'price' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
             try 
             {
                $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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
                
                $isExist = $sabeel->where(['id'=> $sabeel_id])->first();
                if(empty($isExist))
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.sabeel_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                } else 
                {
                    // UPDATE MENU IMAGES
                    $photo_url = $isExist['photo'];
                    if(!empty($_FILES["photo"]["tmp_name"]))
                    {
                        $file_path = 'public/assets/uploads/sabeel/';
                        $photo     =  $this->request->getFile('photo');
                        $tempname  = $photo->getRandomName();
                        $photo->move($file_path, $tempname);
                        $photo_url = $file_path . $tempname;
                        unlink($isExist['photo']);
                    }

                    $updateData = array(
                        'name'           =>    $name,
                        'description'    =>    $description,
                        'price'          =>    $price,
                        'photo'          =>    $photo_url,
                        'updated_date'   => date('Y-m-d H:i:s'),
                    );

                    if($sabeel->update($sabeel_id, $updateData))
                    {
                        $data = $sabeel->where( ['id'=> $sabeel_id] )->first();
                        return $service->success([
                                'message'       =>  Lang('Language.update_success'),
                                'data'          =>  $data,
                                ],
                                ResponseInterface::HTTP_CREATED,
                                $this->response
                        );
                    }
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

    // Delete Sabeel by Admin - by Javeriya Kauser
    public function deleteSabeel()
    {
        $service   =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        
        $token            =  $this->request->getVar('authorization');
        $user_role        =  $this->request->getVar('logged_user_role');
        $provider_id      =  $this->request->getVar('provider_id');
        $sabeel_id        =  $this->request->getVar('sabeel_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'provider_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
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
            'sabeel_id' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try 
            {
                $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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

                $isExist = $sabeel->where(['id'=> $sabeel_id])->first();
                if(!empty($isExist))
                {
                    $update = $sabeel->update($sabeel_id, ['status' => '0']);
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
                        'message'   =>  Lang('Language.sabeel_not_found'),
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

    // Add Meals by Admin - by Javeriya Kauser
    public function addMeal()
    {
        $service   =  new Services();
        $meals     = new MealsModel();
        $menuMeals = new MealsMenuModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('authorization');
        $provider_id       =  $this->request->getVar('provider_id');
        $user_role         =  $this->request->getVar('logged_user_role');
        $cuisine_id        =  $this->request->getVar('cuisine_id');
        $title             =  $this->request->getVar('title');
        $no_of_person      =  $this->request->getVar('no_of_person');
        // $cost_per_meals    =  $this->request->getVar('cost_per_meals');
        $cost_per_day      =  $this->request->getVar('cost_per_day');
        $meals_type        =  $this->request->getVar('meals_type');
        $meals_service     =  $this->request->getVar('meals_service');
        $pickup_address    =  $this->request->getVar('pickup_address');
        $cities            =  $this->request->getVar('cities');

        $lat               =  $this->request->getVar('lat');
        $long              =  $this->request->getVar('long');

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
            'cuisine_id' => [
                'rules'         =>  'required|numeric',
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
            'title' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'no_of_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'cost_per_day' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'meals_service' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'cities' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try 
            {
                $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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
                
                $image_1  =  $this->request->getFile('image_1');
                $image_2  =  $this->request->getFile('image_2');
                $image_3  =  $this->request->getFile('image_3');
                $fileExtension3 = $image_3->getExtension();

                $validated = $this->validate([
                    'file' => [
                        'uploaded[menu_image]',
                        'mime_in[menu_image,image/jpg,image/jpeg,image/png]',
                        'max_size[menu_image,5120]',
                    ],
                ]);

                if($validated)
                {   

                    $file_path = 'public/assets/uploads/meals/';
                    // Image 1
                    $newName1 = $image_1->getRandomName();

                    $image_1->move($file_path, $newName1);
                    $img1 = $file_path . $newName1;

                    // Image 2
                    $newName2 = $image_2->getRandomName();
                    $image_2->move($file_path, $newName2);

                    $img2 = $file_path . $newName2;

                    // Image 3
                    $newName3 = $image_3->getRandomName();
                    $image_3->move($file_path, $newName3);
                    $img3 = $file_path . $newName3;

                    $small_thumbnail_path = "public/assets/thumbnail/";
                    $this->createFolder($small_thumbnail_path);
                    $small_thumbnail = $small_thumbnail_path . $newName3;
                    $thumb_url = $this->createThumbnail($img3, $small_thumbnail, $fileExtension3, 150, 93);

                    $data = array(
                        'title'          => $title,
                        'cuisine_id'     => $cuisine_id,
                        'menu_url'       => '',
                        'no_of_person'   => $no_of_person,
                        // 'cost_per_meals' => $cost_per_meals, 
                        'cost_per_day'   => $cost_per_day,
                        'meals_type'     => $meals_type,
                        'meals_service'  => $meals_service,
                        'pickup_address' => (isset($pickup_address))?$pickup_address:'',
                        'cities'         => (isset($cities))?$cities:'',
                        'img_url_1'      => $img1,
                        'img_url_2'      => $img2,
                        'img_url_3'      => $img3,
                        'thumbnail_url'  => $thumb_url,
                        'provider_id'    => $provider_id,
                        'created_date'   => date('Y-m-d H:i:s'),
                        'provider_lat'   => $lat,
                        'provider_long'   => $long,
                    );

                    $meals_id = $meals->insert($data);
                    
                    if($meals_id) 
                    {
                        // SAVE MULTIPLE IMAGE
                        // $meals_id = $meals->insertID();

                        foreach ($this->request->getFileMultiple('menu_image') as $file) 
                        {
                            $file_path = 'public/assets/uploads/meals/menus/';
                            if (!file_exists($file_path)) {
                                mkdir($file_path, 0777, true);
                            }

                            $newName = $file->getRandomName();
                            $file->move($file_path, $newName);
                            $menu = $file_path . $newName;

                            $menuImage = [
                                'meals_id'   => $meals_id,
                                'menu_url'   => $menu,
                                'created_date' => date('Y-m-d H:i:s'),
                            ];
                            $save = $menuMeals->insert($menuImage);
                        }

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
                }
                else {
                    return $service->fail(
                        [
                            'errors'     =>  $this->validator->getErrors(),
                            'message'   =>  Lang('Language.upload_failed'),
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
                    ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
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

    // Update Meals by Admin - by Javeriya Kauser
    public function updateMeal()
    {
        $service        =  new Services();
        $meals          = new MealsModel();
        $ProviderModel  = new ProviderModel();
        $menuMeals      = new MealsMenuModel();
        $service->cors();

        // $pageNo            =  $this->request->getVar('pageNo');
        $token            =  $this->request->getVar('authorization');
        $user_role        =  $this->request->getVar('logged_user_role');
        $provider_id      =  $this->request->getVar('provider_id');
        $meals_id          =  $this->request->getVar('meals_id');
        $cuisine_id        =  $this->request->getVar('cuisine_id');
        $title             =  $this->request->getVar('title');
        $no_of_person      =  $this->request->getVar('no_of_person');
        // $cost_per_meals    =  $this->request->getVar('cost_per_meals');
        $cost_per_day      =  $this->request->getVar('cost_per_day');
        $meals_type        =  $this->request->getVar('meals_type');
        $meals_service     =  $this->request->getVar('meals_service');
        $pickup_address    =  $this->request->getVar('pickup_address');
        $cities            =  $this->request->getVar('cities');
        $provider_lat      =  $this->request->getVar('lat');
        $provider_long     =  $this->request->getVar('long');

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
            'cuisine_id' => [
                'rules'         =>  'required|numeric',
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
            'title' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'no_of_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            // 'cost_per_meals' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
            'cost_per_day' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'meals_service' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'cities' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try 
            {
                $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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
                
                $isExist = $meals->where(['id'=> $meals_id])->first();
                if(empty($isExist))
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.meal_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                } else 
                {
                    // $old_menu = $isExist['menu_url'];
                    $menu  =  $this->request->getFile('menu_image');

                    // if(!empty($_FILES["menu_image"]["tmp_name"]))
                    // {
                    //     $validated = $this->validate([
                    //         'file' => [
                    //             'uploaded[menu_image]',
                    //             'mime_in[menu_image,image/jpg,image/jpeg,image/gif,image/png]',
                    //             'max_size[menu_image,5120]',
                    //         ],
                    //     ]);

                    //     if($validated && !$menu->hasMoved())
                    //     {
                    //         $file_path = 'public/assets/uploads/meals/';
                    //         if (!file_exists($file_path)) {
                    //             mkdir($file_path, 0777, true);
                    //         }

                    //         $newName = $menu->getRandomName();
                    //         $menu->move($file_path, $newName);
                    //         $picture = $file_path . $newName;

                    //         if (!empty($isExist['menu_url']) && file_exists($isExist['menu_url'])) {
                    //             unlink($isExist['menu_url']);
                    //         }
                    //     }
                    //     $menu = $picture;
                    // } else { $menu = $old_menu; }

                    $updateData = array(
                        'title'          => $title,
                        'cuisine_id'     => $cuisine_id,
                        'menu_url'       => $menu,
                        'no_of_person'   => $no_of_person,
                        // 'cost_per_meals' => $cost_per_meals, 
                        'cost_per_day'   => $cost_per_day,
                        'meals_type'     => $meals_type,
                        'meals_service'  => $meals_service,
                        'pickup_address' => (isset($pickup_address))?$pickup_address:'',
                        'cities'         => (isset($cities))?$cities:'',
                        'provider_id'    => $provider_id,
                        'provider_lat'   => $provider_lat ? $provider_lat : '',   
                        'provider_long'  => $provider_long ? $provider_long : '',   
                        'updated_date'   => date('Y-m-d H:i:s')
                    );

                    if($meals->update($meals_id, $updateData))
                    {
                        // UPDATE MENU IMAGES
                        if(!empty($_FILES["menu_image"]["tmp_name"]))
                        {
                            $oldImageDelete = $menuMeals->where("meals_id", $meals_id)->delete();
                            foreach ($this->request->getFileMultiple('menu_image') as $file) 
                            {
                                $file_path = 'public/assets/uploads/meals/menus/';
                                if (!file_exists($file_path)) {
                                    mkdir($file_path, 0777, true);
                                }

                                $newName = $file->getRandomName();
                                $file->move($file_path, $newName);
                                $menu = $file_path . $newName;

                                $menuImage = [
                                    'meals_id'   => $meals_id,
                                    'menu_url'   => $menu,
                                    'created_date' => date('Y-m-d H:i:s'),
                                ];
                                $save = $menuMeals->insert($menuImage);
                            }
                        }

                        $data = $meals->where( ['id'=> $meals_id] )->first();
                        return $service->success([
                                'message'       =>  Lang('Language.update_success'),
                                'data'          =>  $data,
                                ],
                                ResponseInterface::HTTP_CREATED,
                                $this->response
                        );
                    }
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

    // Delete Meals by Admin - by Javeriya Kauser
    public function deleteMeal()
    {
        $service   =  new Services();
        $meals     = new MealsModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        $token            =  $this->request->getVar('authorization');
        $user_role        =  $this->request->getVar('logged_user_role');
        $provider_id      =  $this->request->getVar('provider_id');
        $meals_id         =  $this->request->getVar('meals_id');

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
            'meals_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'provider_id' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try {
                    $provider_data = $ProviderModel->where("id", $provider_id)->where("status", 'active')->first();
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

                 $isExist = $meals->where(['id'=> $meals_id])->first();
                 if(!empty($isExist))
                 {
                    $update = $meals->update($meals_id, ['status' => 'deleted']);
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
                            'message'   =>  Lang('Language.meal_not_found'),
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

    function createThumbnail($sourcePath, $targetPath, $file_type, $thumbWidth, $thumbHeight)
    {
        if ($file_type == "png") {
            $source = imagecreatefrompng($sourcePath);
        } else {
            $source = imagecreatefromjpeg($sourcePath);
        }
        $width = imagesx($source);
        $height = imagesy($source);

        $tnumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($tnumbImage, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        if (imagejpeg($tnumbImage, $targetPath, 90)) 
        {
            imagedestroy($tnumbImage);
            imagedestroy($source);
            return $targetPath;
        } else {
            return FALSE;
        }
    }

    function createFolder($path)
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, TRUE);
        }
    }
}
