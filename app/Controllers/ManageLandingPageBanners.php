<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LandingPageBanners;
use App\Models\ZiyaratPoints;
use App\Models\ZiyaratPointsImages;
use Exception;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ManageLandingPageBanners extends BaseController
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

    public function bannerList()
    {
        $service           =  new Services();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');

        $search           =  $this->request->getVar('search');

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

            $db = db_connect();
            $table = $db->table('tbl_landing_page_banners as banner')->join('tbl_package as package','package.id = banner.package_id')->where('banner.status !=', 'deleted');

            if (isset($search) && !empty($search)) {
                $table->orLike('banner.title', $search);
                $table->orLike('banner.description', $search);            
            }
            
            // Clone the builder to use for total count query
            $totalBuilder = clone $table;

            // Calculate the total count
            $total = $totalBuilder->countAllResults(false);

            $data = $table->orderBy('banner.id', 'DESC')
                        ->select("banner.*, package.package_title")
                        ->limit($limit, $offset)
                        ->get()
                        ->getResult(); 
                

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                        'total'     =>  $total,
                        'data'      =>  $data,
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

    public function addBanner()
    {
        $service        =  new Services();
        $service->cors();

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'package_id' => [
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

            'description' => [
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
            
            $package_id   =  $this->request->getVar('package_id');
            $title        =  $this->request->getVar('title');
            $description  =  $this->request->getVar('description');
                
            if (isset($_FILES) && !empty($_FILES)) {
                $file = $this->request->getFile('image');
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                } else {
                    $path = 'public/assets/uploads/banners/';
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                }
            } else {
                echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
                die();
            }
            
            $data = array(
                'package_id'     =>  $package_id,
                'title'          =>  $title,
                'description'    =>  $description,
                'image'          =>  $path . $newName,  
                'created_at'     =>  date('Y-m-d H:i:s'),
            );

            $db = db_connect();
            $insert = $db->table('tbl_landing_page_banners')->insert($data);

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

    public function viewBanner()
    {
        $bannerModel    =  new LandingPageBanners();
        $service        =  new Services();
        $service->cors();

        $banner_id     =  $this->request->getVar('banner_id');

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
            'banner_id' => [
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
            $bannerDetails = $bannerModel->where("tbl_landing_page_banners.id", $banner_id)
                                         ->where("tbl_landing_page_banners.status !=",'deleted')
                                         ->join('tbl_package as package','package.id = tbl_landing_page_banners.package_id')
                                         ->select('tbl_landing_page_banners.*, package.package_title')
                                         ->first();

            if(!empty($bannerDetails)) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.details_success'),
                        'data'          =>  $bannerDetails
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

    public function editBanner()
    {
        $bannerModel    =  new LandingPageBanners();
        $service        =  new Services();
        $service->cors();

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
            'banner_id' => [
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

            $banner_id      =  $this->request->getVar('banner_id');
            $package_id   =  $this->request->getVar('package_id');
            $title        =  $this->request->getVar('title');
            $description  =  $this->request->getVar('description');

            $bannerDetailsArray = $bannerModel->where("id", $banner_id)->where("status !=",'deleted')->first();
            if (empty($bannerDetailsArray)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Banner Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $bannerDetails = (object)$bannerDetailsArray;

            if ($this->request->getFile('image')) {
                $file = $this->request->getFile('image');
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                } else {
                    $path = 'public/assets/uploads/banners/';
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $url = $path . $newName;
                }
            }else{
                $url = $bannerDetails->image;
            }

            $data = [
                'package_id'     =>  $package_id ? $package_id : $bannerDetails->package_id,
                'title'          =>  $title ? $title : $bannerDetails->title,
                'description'    =>  $description ? $description : $bannerDetails->description,
                "image"          =>  $url,
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            $db = db_connect();
            $update = $db->table('tbl_landing_page_banners')
                ->where('id', $banner_id)
                ->update($data);

            if($update) 
            {
                $bannerData = $bannerModel->where("id", $banner_id)->where("status !=",'deleted')->first();

                return $service->success([
                        'message'       =>  Lang('Language.update_success'),
                        'data'          =>  $bannerData
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

    public function deleteBanner()
    {
        $bannerModel    =  new LandingPageBanners();
        $service        =  new Services();
        $service->cors();

        $banner_id     =  $this->request->getVar('banner_id');

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
            'banner_id' => [
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
            $bannerDetails = $bannerModel->where("id", $banner_id)->where("status !=",'deleted')->first();
            if (empty($bannerDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Banneer Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $delete = $db->table('tbl_landing_page_banners')
                ->where('id', $banner_id)
                ->set('status', 'deleted')
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

    public function changeBannerStatus()
    {
        $bannerModel    =  new LandingPageBanners();
        $service        =  new Services();
        $service->cors();

        $banner_id     =  $this->request->getVar('banner_id');
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
            'banner_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'status' => [
                'rules'         =>  'required|in_list[active,inactive]',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', ['active','inactive']),
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
            $bannerDetails = $bannerModel->where("id", $banner_id)->where("status !=",'deleted')->first();
            if (empty($bannerDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Banneer Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $update = $db->table('tbl_landing_page_banners')
                ->where('id', $banner_id)
                ->set('status', $status)
                ->update();

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.Banner status changed successfully'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Unable to change Banner status, please try again'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.Unable to change Banner status, please try again'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
