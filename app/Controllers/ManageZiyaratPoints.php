<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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

class ManageZiyaratPoints extends BaseController
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

    public function pointList()
    {
        $service           =  new Services();
        $pointImageModel   = new ZiyaratPointsImages();
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
            $table = $db->table('tbl_ziyarat_points as e')->join('tbl_city_master as s','s.id = e.city_id')->where('e.status !=', '2');

            if (isset($search) && !empty($search)) {
                $table->groupStart();
                $table->like('e.name_en', $search);
                $table->orLike('e.title_en', $search);
                $table->orLike('s.name', $search);
                $table->groupEnd();
            }
            
            // Clone the builder to use for total count query
            $totalBuilder = clone $table;

            // Calculate the total count
            $total = $totalBuilder->countAllResults(false);

            $data = $table->orderBy('e.id', 'DESC')
                        ->select("e.*, s.name as city_name")
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

    public function addPoint()
    {
        $pointModel        =  new ZiyaratPoints();
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
            'city_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],

            // 'name_en' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],

            // 'name_ur' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],

            'title_en' => [
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

            'description_en' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            
            'description_ur' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],

            'address' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],

            'lat' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],

            'long' => [
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
            
            $city_id        =  $this->request->getVar('city_id');
            $name_en        =  $this->request->getVar('name_en');
            $name_ur        =  $this->request->getVar('name_ur');
            $title_en       =  $this->request->getVar('title_en');
            $title_ur       =  $this->request->getVar('title_ur');
            $description_en   =  $this->request->getVar('description_en');
            $description_ur   =  $this->request->getVar('description_ur');
            $address        = $this->request->getVar('address');
            $lat         =  $this->request->getVar('lat');
            $long        =  $this->request->getVar('long');
            $video       = $this->request->getVar('video');
                
            if (isset($_FILES) && !empty($_FILES)) {
                $file = $this->request->getFile('main_img');
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                } else {
                    $path = 'public/assets/uploads/ziayarat_points/main_pic/';
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                }
            } else {
                echo json_encode(['status' => 'failed', 'messages' => lang('Language.Images required')]);
                die();
            }

            // $videoFile = $this->request->getFile('video');
            // if ($videoFile) {
            //     $videoPath = 'public/assets/uploads/ziayarat_points/video/';
            //     $videoName = $videoFile->getRandomName();
            //     $videoFile->move($videoPath, $videoName); 
            //     $url = $videoPath . $videoName;               
            // }else{
            //     $url = '';
            // }
            

            $data = array(
                'city_id'           =>    $city_id,
                'name_en'           =>    $name_en ? $name_en : "",
                'name_ur'           =>    $name_ur ? $name_ur : "",
                'title_en'          =>    $title_en,
                'title_ur'          =>    $title_ur,
                'description_en'    =>    $description_en,
                'description_ur'    =>    $description_ur,
                'address'           =>    $address,
                'lat'               =>    $lat,
                'long'              =>    $long,
                'main_img'          =>  $path . $newName,  
                'video'             =>  $video ? $video : "" ,              
                'created_at'        => date('Y-m-d H:i:s'),
            );

            $db = db_connect();
            $insert = $db->table('tbl_ziyarat_points')->insert($data);
            $point_id = $db->insertID();

            if($insert) 
            {
                foreach ($this->request->getFileMultiple('image_array') as $file) {
                    $point_pic_path = 'public/assets/uploads/ziayarat_points/point_pic/';
                    $new_name = $file->getRandomName();
                    $data = [
                        'point_id' => $point_id,
                        'image' => $point_pic_path . $new_name,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $save = $db->table('tbl_ziyarat_point_images')->insert($data);
                    $file->move($point_pic_path, $new_name);
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

    public function viewPoint()
    {
        $pointModel        =  new ZiyaratPoints();
        $pointImageModel   = new ZiyaratPointsImages();
        $service        =  new Services();
        $service->cors();

        $point_id            =  $this->request->getVar('point_id');

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
            'point_id' => [
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
            $pointDetails = $pointModel->where("tbl_ziyarat_points.id", $point_id)
                                        ->where("tbl_ziyarat_points.status !=",'2')
                                        ->join('tbl_city_master as c','c.id = tbl_ziyarat_points.city_id')
                                        ->select('tbl_ziyarat_points.*, c.name as city_name')
                                        ->first();

            if(!empty($pointDetails)) 
            {
                $pointDetails['images'] = $pointImageModel->where("point_id", $point_id)->findAll();
                return $service->success([
                        'message'       =>  Lang('Language.details_success'),
                        'data'          =>  $pointDetails
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

    public function editPoint()
    {
        $pointModel        =  new ZiyaratPoints();
        $pointImageModel   = new ZiyaratPointsImages();
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
            'point_id' => [
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

            $point_id       =  $this->request->getVar('point_id');
            $city_id        =  $this->request->getVar('city_id');
            $name_en        =  $this->request->getVar('name_en');
            $name_ur        =  $this->request->getVar('name_ur');
            $title_en       =  $this->request->getVar('title_en');
            $title_ur       =  $this->request->getVar('title_ur');
            $description_en   =  $this->request->getVar('description_en');
            $description_ur   =  $this->request->getVar('description_ur');
            $address        = $this->request->getVar('address');
            $lat         =  $this->request->getVar('lat');
            $long        =  $this->request->getVar('long');
            $video       = $this->request->getVar('video');
            $images      = $this->request->getFileMultiple('image_array');

            $pointDetailsArray = $pointModel->where("id", $point_id)->where("status !=",'2')->first();
            if (empty($pointDetailsArray)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Ziyarat Point Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $pointDetails = (object)$pointDetailsArray;

            if ($this->request->getFile('main_img')) {
                $file = $this->request->getFile('main_img');
                if (!$file->isValid()) {
                    throw new RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                } else {
                    $path = 'public/assets/uploads/ziayarat_points/main_pic/';
                    $newName = $file->getRandomName();
                    $file->move($path, $newName);
                    $url = $path . $newName;
                }
            }else{
                $url = $pointDetails->main_img;
            }

            // $videoFile = $this->request->getFile('video');
            // if ($videoFile) {
            //     $videoPath = 'public/assets/uploads/ziayarat_points/video/';
            //     $videoName = $videoFile->getRandomName();
            //     $videoFile->move($videoPath, $videoName);
            //     $videoUrl = $videoPath. $videoName;                
            // } else {
            //     $videoUrl = $pointDetails->video;
            // }

            $data = [
                "city_id" => $city_id ? $city_id : $pointDetails->city_id,
                "name_en" => $name_en ? $name_en : ($pointDetails->name_en ? $pointDetails->name_en : ""),
                "name_ur" => $name_ur ? $name_ur : ($pointDetails->name_ur ? $pointDetails->name_ur : ""),
                "title_en" => $title_en ? $title_en : $pointDetails->title_en,
                "title_ur" => $title_ur ? $title_ur : $pointDetails->title_ur,
                "description_en" => $description_en ? $description_en : $pointDetails->description_en,
                "description_ur" => $description_ur ? $description_ur : $pointDetails->description_ur,
                "address" => $address ? $address : $pointDetails->address,
                "lat"  => $lat ? $lat : $pointDetails->lat,
                "long" => $long ? $long : $pointDetails->long,
                "main_img" =>  $url,
                "video" =>  $video ? $video : $pointDetails->video,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $db = db_connect();
            $update = $db->table('tbl_ziyarat_points')
                ->where('id', $point_id)
                ->update($data);

            if($update) 
            {
                if ($images) {
                    $imgs = $db->table('tbl_ziyarat_point_images')->where('point_id', $point_id)->delete();
                    foreach ($this->request->getFileMultiple('image_array') as $file) {
                        $point_pic_path = 'public/assets/uploads/ziayarat_points/point_pic/';
                        $new_name = $file->getRandomName();
                        $data = [
                            'point_id' => $point_id,
                            'image' => $point_pic_path . $new_name,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $save = $pointImageModel->insert($data);
                        $file->move($point_pic_path, $new_name);
                    } 
                }

                $pointData = $pointModel->where("id", $point_id)->where("status !=",'2')->first();

                if(!empty($pointData)) 
                {
                    $pointData['images'] = $pointImageModel->where("point_id", $point_id)->findAll();
                }

                return $service->success([
                        'message'       =>  Lang('Language.update_success'),
                        'data'          =>  $pointData
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

    public function deletePoint()
    {
        $pointModel        =  new ZiyaratPoints();
        $pointImageModel   = new ZiyaratPointsImages();
        $service        =  new Services();
        $service->cors();

        $point_id            =  $this->request->getVar('point_id');

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
            'point_id' => [
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
            $pointDetails = $pointModel->where("id", $point_id)->where("status !=",'2')->first();
            if (empty($pointDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Ziyarat Point Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $delete = $db->table('tbl_ziyarat_points')
                ->where('id', $point_id)
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

    public function changePointStatus()
    {
        $pointModel        =  new ZiyaratPoints();
        $pointImageModel   = new ZiyaratPointsImages();
        $service        =  new Services();
        $service->cors();

        $point_id            =  $this->request->getVar('point_id');
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
            'point_id' => [
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
            $pointDetails = $pointModel->where("id", $point_id)->where("status !=",'2')->first();
            if (empty($pointDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Ziyarat Point Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $update = $db->table('tbl_ziyarat_points')
                ->where('id', $point_id)
                ->set('status', $status)
                ->update();

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.Ziyarat Point status changed successfully'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Unable to change Ziyarat Point status, please try again'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.Unable to change Ziyarat Point status, please try again'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
