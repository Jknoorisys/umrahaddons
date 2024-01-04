<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\Visa;
use App\Models\VisaEnquiry;
use Exception;

use Config\Services;

use CodeIgniter\HTTP\ResponseInterface;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ManageVisa extends BaseController
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

    public function index()
    {
        $service           =  new Services();
        $service->cors();

        $user_role        =  $this->request->getVar('logged_user_role');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_role' => [
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

        try{

            $whereCondition = "";

            if($user_role == 'admin'){ $whereCondition .= "s.status != '2'"; } 

            if($user_role == 'user'){ $whereCondition .= "s.status = '1'"; } 

            if($user_role == 'provider'){ $whereCondition .= "s.status = '1'"; }

            // By Query Builder
            $db = db_connect();
            $visaPrice = $db->table('tbl_visa as s')
                ->select('s.*')
                ->where($whereCondition)
                ->orderBy('s.id', 'DESC')
                ->get()->getRow();

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  $visaPrice
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

    public function viewVisa()
    {
        $visaModel        =  new Visa();
        $service        =  new Services();
        $service->cors();

        $price_id            =  $this->request->getVar('price_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'price_id' => [
                'rules'         =>  'required|numeric',
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

        try {
            $visaDetails = $visaModel->where("id", $price_id)->where("status !=",'2')->first();

            if(!empty($visaDetails)) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.details_success'),
                        'data'          =>  $visaDetails
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.details_fetch_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.details_fetch_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function editVisa()
    {
        $visaModel        =  new Visa();
        $service        =  new Services();
        $service->cors();

        $price_id          =  $this->request->getVar('price_id');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $logged_user_type  =  $this->request->getVar('logged_user_type');

        $currency    =  $this->request->getVar('currency');
        $price       =  $this->request->getVar('price');
        $duration    =  $this->request->getVar('duration');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'price_id' => [
                'rules'         =>  'required|numeric',
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

        try {
            $visaDetails = $visaModel->where("id", $price_id)->where("status !=",'2')->first();
            if (empty($visaDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Visa Price Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $data = [
                'currency'  =>    $currency ? $currency : $visaDetails['currency'],
                'price'     =>    $price ? $price : $visaDetails['price'],
                'duration'  =>    $duration ? $duration : $visaDetails['duration'],
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $db = db_connect();
            $update = $db->table('tbl_visa')
                ->where('id', $price_id)
                ->update($data);

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.update_success'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
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

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.update_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function enquiryList()
    {
        $service   =  new Services();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('logged_user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $rules = [
            'pageNo' => [
                'rules'         =>  'required|greater_than[' . PAGE_LENGTH . ']|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'greater_than'  =>  Lang('Language.greater_than', [PAGE_LENGTH]),
                    'numeric'       =>  Lang('Language.numeric', [$pageNo]),
                ]
            ],
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
       
        try{
            $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
            $offset        = ( $currentPage - 1 ) * PER_PAGE;
            $limit         =  PER_PAGE;

            $whereCondition = '';

            if($user_role == 'admin'){ $whereCondition .= "e.status = '1'"; }

            elseif($user_role == 'user'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = '1'"; }

            $db = db_connect();
            $table = $db->table('tbl_visa_enquiry as e')
                ->join('tbl_user as u','u.id = e.user_id')
                ->select("e.*, CONCAT(u.firstname,' ',u.lastname) as user_name")
                ->where($whereCondition);

            // Clone the builder to use for total count query
            $totalBuilder = clone $table;

            // Calculate the total count
            $total = $totalBuilder->countAllResults(false);

            $data = $table
                // ->orderBy('e.id', 'DESC')
                ->orderBy("CASE WHEN booking_status = 'pending' THEN 1 ELSE 2 END")
                ->orderBy('created_at', 'DESC')  
                ->limit($limit, $offset)
                ->get()->getResult();
                
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
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.fetch_list'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function addEnquiry()
    {
        $service   =  new Services();
        $service->cors();

        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('logged_user_role');

        $ota_id            =  $this->request->getVar('ota_id');
        $name              =  $this->request->getVar('name');
        $country_code      =  $this->request->getVar('country_code');
        $mobile            =  $this->request->getVar('mobile');
        $no_of_persons     =  $this->request->getVar('no_of_persons');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'logged_user_id' => [
                'rules'         =>  'required|numeric',
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
            'ota_id' => [
                'rules'         =>  'required|numeric',
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
            'no_of_persons' => [
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

        try {
            $data = array(
                'user_id'       => $logged_user_id,
                'ota_id'        => $ota_id,
                'name'          => (isset($name)) ? $name: '',
                'country_code'  => (isset($country_code)) ? $country_code : '',
                'mobile'        => (isset($mobile)) ? $mobile : '',
                'no_of_persons' => (isset($no_of_persons)) ? $no_of_persons : '',
                'booking_status'  => 'pending',
                'created_at'  => date('Y-m-d H:i:s')
            );

            $db = db_connect();
            $visaEnquiry = $db->table('tbl_visa_enquiry')->insert($data);

            if($visaEnquiry) 
            {
                // PUSH NOTIFICATION
                helper('notifications');
                $db = db_connect();
                $userinfo = $db->table('tbl_user')
                    ->select('*')
                    ->where('id', $_POST['logged_user_id'])
                    ->get()->getRow();

                $title = "Visa Enquiry";
                $message = "Your Enquiry has been sent. Thank you.";
                $fmc_ids = array($userinfo->device_token);
                
                $notification = array(
                    'title' => $title ,
                    'message' => $message,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                    'date' => date('Y-m-d H:i'),
                );
                if($userinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }
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
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.add_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function viewEnquiry()
    {
        $service   =  new Services();
        $service->cors();

        $user_role        =  $this->request->getVar('logged_user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $enquiry_id       =  $this->request->getVar('enquiry_id');

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
            'enquiry_id' => [
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
       
        try {
            $db = db_connect();
            $info = $db->table('tbl_visa_enquiry as e')
                ->join('tbl_user as u','u.id = e.user_id')
                ->select("e.*, CONCAT(u.firstname,' ',u.lastname) as user_name")
                ->where('e.status','1')
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
                        'message'   =>  Lang('Language.details_fetch_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.details_fetch_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
