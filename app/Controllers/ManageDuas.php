<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Duas;
use Exception;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");


class ManageDuas extends BaseController
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

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('logged_user_role');

        $search           =  $this->request->getVar('search');
        $type           =  $this->request->getVar('type');
        $language = $this->request->getVar('language');

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

            $whereCondition = "";

            if(isset($search) && $search!=''){
                $whereCondition .= "s.title LIKE'%" . $search . "%' AND ";
            }

            if(isset($type) && $type!=''){
                $whereCondition .= "s.type = '" . $type . "' AND "; 
            }

            if($user_role == 'admin'){ $whereCondition .= "s.status != '2'"; } 

            if($user_role == 'user'){ $whereCondition .= "s.status = '1'"; } 

            if($user_role == 'provider'){ $whereCondition .= "s.status = '1'"; }

            // By Query Builder
            $db = db_connect();
            if ($language == 'en') {
                $duasData = $db->table('tbl_duas as s')
                                ->select('s.id, s.user_id, s.user_type, s.title_en as title, s.reference_en as reference, s.image, s.type, s.status, s.created_at, s.updated_at')
                                ->where($whereCondition)
                                ->orderBy('s.id', 'DESC')
                                ->limit($limit, $offset)
                                ->get()->getResult();
            } else {
                $duasData = $db->table('tbl_duas as s')
                                ->select('s.id, s.user_id, s.user_type, s.title_ur as title, s.reference_ur as reference, s.image, s.type, s.status, s.created_at, s.updated_at')
                                ->where($whereCondition)
                                ->orderBy('s.id', 'DESC')
                                ->limit($limit, $offset)
                                ->get()->getResult();
            }
            

            $total =  $db->table('tbl_duas as s')->where($whereCondition)->countAllResults();

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                        'total'             =>  $total,
                        'duasList'         =>  $duasData,
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

    public function addDuas()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $logged_user_type  =  $this->request->getVar('logged_user_role');

        $title_en        =  $this->request->getVar('title_en');
        $reference_en    =  $this->request->getVar('reference_en');
        $title_ur        =  $this->request->getVar('title_ur');
        $reference_ur    =  $this->request->getVar('reference_ur');

        $type         =  $this->request->getVar('type');

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
            'title_en' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'reference_en' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'title_ur' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'reference_ur' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'type' => [
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
            
            if ($this->request->getFile('image')) {
                $file_path = 'public/assets/uploads/duas/';
                $photo  =  $this->request->getFile('image');
                $tempname  = $photo->getRandomName();
                $photo->move($file_path, $tempname);
                $photo_url = $file_path . $tempname;
            }

            $data = array(
                'user_id'       =>    $logged_user_id,
                'user_type'     =>    $logged_user_type,
                'title_en'      =>    $title_en,
                'reference_en'  =>    $reference_en,
                'title_ur'      =>    $title_ur,
                'reference_ur'  =>    $reference_ur,
                'type'          =>    $type,
                'image'         =>    $photo_url ? $photo_url : '',
                'created_at'    => date('Y-m-d H:i:s'),
            );

            $db = db_connect();
            $insert = $db->table('tbl_duas')->insert($data);
            if($insert) 
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

    public function viewDua()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $dua_id            =  $this->request->getVar('dua_id');

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
            'dua_id' => [
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
            $duaDetails = $duaModel->where("id", $dua_id)->where("status !=",'2')->first();

            if(!empty($duaDetails)) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.details_success'),
                        'data'          =>  $duaDetails
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

    public function editDuas()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $dua_id            =  $this->request->getVar('dua_id');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $logged_user_type  =  $this->request->getVar('logged_user_type');

        $title_en        =  $this->request->getVar('title_en');
        $reference_en    =  $this->request->getVar('reference_en');
        $title_ur        =  $this->request->getVar('title_ur');
        $reference_ur    =  $this->request->getVar('reference_ur');
        $type         =  $this->request->getVar('type');

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
            'dua_id' => [
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
            $duaDetails = $duaModel->where("id", $dua_id)->where("status !=",'2')->first();
            if (empty($duaDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Dua Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            if ($this->request->getFile('image')) {
                    $file_path = 'public/assets/uploads/duas/';
                    $photo  =  $this->request->getFile('image');
                    $tempname  = $photo->getRandomName();
                    $photo->move($file_path, $tempname);
                    $photo_url = $file_path . $tempname;
            }else{
                $photo_url = $duaDetails['image'];
            }

            $data = [
                'title_en'      =>    $title_en ? $title_en : $duaDetails['title_en'],
                'reference_en'  =>    $reference_en ? $reference_en : $duaDetails['reference_en'],
                'title_ur'      =>    $title_ur ? $title_ur : $duaDetails['title_ur'],
                'reference_ur'  =>    $reference_ur ? $reference_ur : $duaDetails['reference_ur'],
                'type'          =>    $type ? $type : $duaDetails['type'],
                'image'         =>    $photo_url ? $photo_url : $duaDetails['image'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $db = db_connect();
            $update = $db->table('tbl_duas')
                ->where('id', $dua_id)
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

    public function deleteDuas()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $dua_id            =  $this->request->getVar('dua_id');

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
            'dua_id' => [
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
            $duaDetails = $duaModel->where("id", $dua_id)->where("status !=",'2')->first();
            if (empty($duaDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Dua Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $delete = $db->table('tbl_duas')
                ->where('id', $dua_id)
                ->set('status', '2')
                ->update();

            if($delete) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.delete_success'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.delete_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.delete_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    public function changeDuaStatus()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $dua_id            =  $this->request->getVar('dua_id');
        $status            =  $this->request->getVar('status');

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
            'dua_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'status' => [
                'rules'         =>  'required|in_list[0,1]',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [0,1]),
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
            $duaDetails = $duaModel->where("id", $dua_id)->where("status !=",'2')->first();
            if (empty($duaDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Dua Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $update = $db->table('tbl_duas')
                ->where('id', $dua_id)
                ->set('status', $status)
                ->update();

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.Dua status changed successfully'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Unable to change Dua status, please try again'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.Unable to change Dua status, please try again'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
