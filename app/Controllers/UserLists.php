<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\Duas;
use App\Models\FullPackage;
use App\Models\FullPackageDates;
use App\Models\FullPackageImages;
use App\Models\Visa;
use App\Models\ZiyaratPoints;
use App\Models\ZiyaratPointsImages;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

use Config\Services;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class UserLists extends BaseController
{
    private $service;
	
	public function __construct()
	{
		$this->service  = new Services();
		helper('auth');

        $lang = (isset($_POST["language"]) && !empty($_POST["language"])) ? $_POST["language"] : '';
		if (!empty($lang)) {
			$language = \Config\Services::language();
			$language->setLocale($lang);
		} else {
			echo json_encode(['status' => 403, 'messages' => 'Language is Required']);
			die();
		}
	}

   	// Dua List by Javeriya
	public function listOfDua()
    {
        $service           =  new Services();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  'user';

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
                        'list'         =>  $duasData,
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

	// view Dua by Javeriya
	public function viewDua()
    {
        $duaModel        =  new Duas();
        $service        =  new Services();
        $service->cors();

        $dua_id            =  $this->request->getVar('dua_id');
        $language           = $this->request->getVar('language');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
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

             // By Query Builder
             $db = db_connect();

            if ($language == 'en') {
                $duaDetails = $db->table('tbl_duas as s')
                                ->select('s.id, s.user_id, s.user_type, s.title_en as title, s.reference_en as reference, s.image, s.type, s.status, s.created_at, s.updated_at')
                                ->where("id", $dua_id)
                                ->where("status",'1')
                                ->get()->getRow();
            } else {
                $duaDetails = $db->table('tbl_duas as s')
                                ->select('s.id, s.user_id, s.user_type, s.title_ur as title, s.reference_ur as reference, s.image, s.type, s.status, s.created_at, s.updated_at')
                                ->where("id", $dua_id)
                                ->where("status",'1')
                                ->get()->getRow();
            }

            // $duaDetails = $duaModel->where("id", $dua_id)->where("status",'1')->first();

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

	// Visa Price by Javeriya
	public function listOfVisaPrice()
    {
        $service           =  new Services();
        $service->cors();

        $user_role        =  'user';

        $rules = [
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

	// View Visa Price by Javeriya
    public function viewVisaPrice()
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

    // full package list- by Javeriya Kauser
    // public function packageList(){
    //     $service   =  new Services();
    //     $service->cors();

    //     $pageNo           =  $this->request->getVar('pageNo');

    //     $rules = [
    //         'pageNo' => [
    //             'rules'         =>  'required|greater_than[' . PAGE_LENGTH . ']|numeric',
    //             'errors'        => [
    //                 'required'      =>  Lang('Language.required'),
    //                 'greater_than'  =>  Lang('Language.greater_than', [PAGE_LENGTH]),
    //                 'numeric'       =>  Lang('Language.numeric', [$pageNo]),
    //             ]
    //         ],
    //         'language' => [
    //             'rules'         =>  'required|in_list[' . LANGUAGES . ']',
    //             'errors'        => [
    //                 'required'      =>  Lang('Language.required'),
    //                 'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
    //             ]
    //         ],
    //     ];

    //     if(!$this->validate($rules)) {
    //         return $service->fail(
    //             [
    //                 'errors'     =>  $this->validator->getErrors(),
    //                 'message'   =>  lang('Language.invalid_inputs')
    //             ],
    //             ResponseInterface::HTTP_BAD_REQUEST,
    //             $this->response
    //         );
    //     }
        
    //     try{

    //         $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
    //         $offset        = ( $currentPage - 1 ) * PER_PAGE;
    //         $limit         =  PER_PAGE;
    //         $search        = $this->request->getVar('search');

    //         $db = db_connect();
    //         $table = $db->table('tbl_full_package as p')->where('p.status', '1');

    //         if (isset($search) && !empty($search)) {
    //             $table->like('p.name', $search);
    //             $table->orLike('p.details', $search);
    //             $table->orLike('p.mecca_hotel', $search);
    //             $table->orLike('p.madinah_hotel', $search);
    //         }

    //         $totalBuilder = clone $table;
    //         $total = $totalBuilder->countAllResults(false);

    //         $data = $table
    //                     ->select('p.id, p.provider_id, p.name, p.duration, p.departure_city, p.mecca_hotel, p.mecca_hotel_distance, p.madinah_hotel, p.madinah_hotel_distance, p.details, p.main_img, p.inclusions, p.pent_rate_SAR as single_rate_SAR, p.pent_rate_INR as single_rate_INR, p.infant_rate_with_bed_SAR, p.infant_rate_with_bed_INR, p.infant_rate_without_bed_SAR, p.infant_rate_without_bed_INR, p.status, p.created_at, p.updated_at')
    //                     ->orderBy('p.id', 'DESC')
    //                     ->limit($limit, $offset)
    //                     ->get()
    //                     ->getResult(); // Fetch the paginated results
                
    //         return $service->success(
    //             [
    //                 'message'       =>  Lang('Language.list_success'),
    //                 'data'          =>  [
    //                     'total'            =>  $total,
    //                     'packages'         =>  $data,
    //                 ]
    //             ],
    //             ResponseInterface::HTTP_OK,
    //             $this->response
    //         );

    //     } catch (Exception $e) {
    //         return $service->fail(
    //             [
    //                 'errors'    =>  $e->getMessage(),
    //                 'message'   =>  Lang('Language.fetch_list'),
    //             ],
    //             ResponseInterface::HTTP_BAD_REQUEST,
    //             $this->response
    //         );
    //     }
    // }
    public function packageList() {
        $service = new Services();
        $service->cors();

        $pageNo = $this->request->getVar('pageNo');

        $rules = [
            'pageNo' => [
                'rules'  => 'required|greater_than[' . PAGE_LENGTH . ']|numeric',
                'errors' => [
                    'required'     => Lang('Language.required'),
                    'greater_than' => Lang('Language.greater_than', [PAGE_LENGTH]),
                    'numeric'      => Lang('Language.numeric', [$pageNo]),
                ]
            ],
            'language' => [
                'rules'  => 'required|in_list[' . LANGUAGES . ']',
                'errors' => [
                    'required' => Lang('Language.required'),
                    'in_list'  => Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return $service->fail(
                [
                    'errors'  => $this->validator->getErrors(),
                    'message' => lang('Language.invalid_inputs')
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }

        try {
            $currentPage = (!empty($pageNo)) ? $pageNo : 1;
            $offset = ($currentPage - 1) * PER_PAGE;
            $limit = PER_PAGE;
            $search = $this->request->getVar('search');

            $db = db_connect();
            $table = $db->table('tbl_full_package as p')->where('p.status', '1');

            if (isset($search) && !empty($search)) {
                $table->groupStart()
                    ->like('p.name', $search)
                    ->orLike('p.details', $search)
                    ->orLike('p.mecca_hotel', $search)
                    ->orLike('p.madinah_hotel', $search)
                    ->groupEnd();
            }

            $totalBuilder = clone $table;
            $total = $totalBuilder->countAllResults(false);

            $data = $table
                ->select('p.id, p.provider_id, p.name, p.duration, p.departure_city, p.mecca_hotel, p.mecca_hotel_distance, p.madinah_hotel, p.madinah_hotel_distance, p.details, p.main_img, p.inclusions, p.pent_rate_SAR as single_rate_SAR, p.pent_rate_INR as single_rate_INR, p.infant_rate_with_bed_SAR, p.infant_rate_with_bed_INR, p.infant_rate_without_bed_SAR, p.infant_rate_without_bed_INR, p.status, p.created_at, p.updated_at')
                ->orderBy('p.id', 'DESC')
                ->limit($limit, $offset)
                ->get()
                ->getResult(); // Fetch the paginated results

            return $service->success(
                [
                    'message' => Lang('Language.list_success'),
                    'data'    => [
                        'total'    => $total,
                        'packages' => $data,
                    ]
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'  => $e->getMessage(),
                    'message' => Lang('Language.fetch_list'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
    
    // view full package- by Javeriya Kauser
    public function viewPackage(){
        $package   =  new FullPackage();
        $dates   =  new FullPackageDates();
        $images   =  new FullPackageImages();
        $service   =  new Services();
        $service->cors();

        $package_id       =  $this->request->getVar('full_package_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'full_package_id' => [
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
            $isExist = $package->where('id',$package_id)->where('status','1')->first();
                if(!empty($isExist))
                {
                $db = db_connect();
                $isExist['departure_dates'] = $db->table('tbl_full_package_dates')->where('full_package_id', $package_id)->get()->getResult();
                $isExist['images'] = $db->table('tbl_full_package_image')->where('full_package_id', $package_id)->get()->getResult();

                return $service->success([
                    'message'       =>  Lang('Language.details_success'),
                    'data'          =>  $isExist
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
                'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.details_fetch_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    // City List by Javeriya
	public function listOfCities()
    {
        $service           =  new Services();
        $service->cors();

        // $pageNo           =  $this->request->getVar('pageNo');

        $search           =  $this->request->getVar('search');

        // $rules = [
        //     'pageNo' => [
        //         'rules'         =>  'required|greater_than[' . PAGE_LENGTH . ']|numeric',
        //         'errors'        => [
        //             'required'      =>  Lang('Language.required'),
        //             'greater_than'  =>  Lang('Language.greater_than', [PAGE_LENGTH]),
        //             'numeric'       =>  Lang('Language.numeric', [$pageNo]),
        //         ]
        //     ]
        // ];

        // if(!$this->validate($rules)) {
        //     return $service->fail(
        //         [
        //             'errors'     =>  $this->validator->getErrors(),
        //             'message'   =>  lang('Language.invalid_inputs')
        //         ],
        //         ResponseInterface::HTTP_BAD_REQUEST,
        //         $this->response
        //     );
        // }

        try{

            // $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
            // $offset        = ( $currentPage - 1 ) * PER_PAGE;
            // $limit         =  PER_PAGE;

            // By Query Builder
            $db = db_connect();
            $table = $db->table('tbl_city_master as c')
                        ->where('c.status', '1');
            
                        
            if(isset($search) && $search!=''){
                $table->like('c.name', $search);            
            }
            
            // Clone the builder to use for total count query
            $totalBuilder = clone $table;

            // Calculate the total count
            $total = $totalBuilder->countAllResults(false);

            $pointData = $table->orderBy('c.id')
                        // ->limit($limit, $offset)
                        ->get()->getResult();

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                    'total'         =>  $total,
                    'city_list'      =>  $pointData,
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

    // Ziyarat Points List by Javeriya
	public function listOfPoint()
    {
        $service           =  new Services();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  'user';

        $search           =  $this->request->getVar('search');
        $language = $this->request->getVar('lang');

        $rules = [
            'pageNo' => [
                'rules'         =>  'required|greater_than[' . PAGE_LENGTH . ']|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'greater_than'  =>  Lang('Language.greater_than', [PAGE_LENGTH]),
                    'numeric'       =>  Lang('Language.numeric', [$pageNo]),
                ]
            ],
            'lang' => [
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
            $city_id       =  $this->request->getVar('city_id');

            $whereCondition = "";

            if($user_role == 'admin'){ $whereCondition .= "s.status != '2'"; } 

            if($user_role == 'user'){ $whereCondition .= "s.status = '1'"; } 

            if($user_role == 'provider'){ $whereCondition .= "s.status = '1'"; }

            // By Query Builder
            $db = db_connect();
            $table = $db->table('tbl_ziyarat_points as s')
                        ->join('tbl_city_master as c','c.id = s.city_id')
                        ->where('city_id', $city_id)
                        ->where($whereCondition);
            
                        
            if(isset($search) && $search!=''){
                $table->like('s.name_en', $search);
                $table->orLike('s.title_en', $search);
                $table->orLike('c.name', $search);            
            }
            
            if ($language == 'en') {
                $table->select('s.id,c.name as city_name, c.image as city_image, s.name_en as name, s.title_en as title, s.description_en as description, s.main_img, s.lat, s.long, s.video, s.status, s.created_at, s.updated_at');
            } else {
                $table->select('s.id,c.name as city_name, c.image as city_image, s.name_ur as name, s.title_ur as title, s.description_ur as description, s.main_img, s.lat, s.long, s.video, s.status, s.created_at, s.updated_at');
            }
            
            // Clone the builder to use for total count query
            $totalBuilder = clone $table;

            // Calculate the total count
            $total = $totalBuilder->countAllResults(false);

            $pointData = $table->orderBy('s.id')
                        ->limit($limit, $offset)
                        ->get()->getResult();


            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                        'total'             =>  $total,
                        'list'         =>  $pointData,
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

	// view Ziyarat Point by Javeriya
	public function viewPoint()
    {
        $pointModel        =  new ZiyaratPoints();
        $pointImageModel   = new ZiyaratPointsImages();
        $service           =  new Services();
        $service->cors();

        $point_id            =  $this->request->getVar('point_id');
        $language = $this->request->getVar('lang');

        $rules = [
            'point_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'lang' => [
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

        try {
           // By Query Builder
           $db = db_connect();
           $table = $db->table('tbl_ziyarat_points as s')
                       ->join('tbl_city_master as c','c.id = s.city_id')
                       ->where('s.id', $point_id);
           
           if ($language == 'en') {
               $table->select('s.id,c.name as city_name, s.name_en as name, s.title_en as title, s.description_en as description, s.main_img, s.lat, s.long, s.video, s.status, s.created_at, s.updated_at');
           } else {
               $table->select('s.id,c.name as city_name, s.name_ur as name, s.title_ur as title, s.description_ur as description, s.main_img, s.lat, s.long, s.video, s.status, s.created_at, s.updated_at');
           }

            $pointDetails = $table->get()->getRow();
            if(!empty($pointDetails)) 
            {
                $pointDetails->images = $pointImageModel->where("point_id", $point_id)->findAll();
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

    // Landing Page Banner List by Javeriya
    public function listOfBanner()
    {
        $service           =  new Services();
        $service->cors();

        // $pageNo           =  $this->request->getVar('pageNo');

        $search           =  $this->request->getVar('search');

        $rules = [
            // 'pageNo' => [
            //     'rules'         =>  'required|greater_than[' . PAGE_LENGTH . ']|numeric',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //         'greater_than'  =>  Lang('Language.greater_than', [PAGE_LENGTH]),
            //         'numeric'       =>  Lang('Language.numeric', [$pageNo]),
            //     ]
            // ],

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

            // $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
            // $offset        = ( $currentPage - 1 ) * PER_PAGE;
            // $limit         =  PER_PAGE;

            $db = db_connect();
            $table = $db->table('tbl_landing_page_banners as banner')->join('tbl_package as package','package.id = banner.package_id')->where('banner.status', 'active');

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
                        // ->limit($limit, $offset)
                        ->get()
                        ->getResult(); 
                

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                        // 'total'     =>  $total,
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

    // Featured Ziyaarat Package List by Javeriya
    public function featuredPackageList() {
        $service = new Services();
        $service->cors();

        // $pageNo = $this->request->getVar('pageNo');

        $rules = [
            // 'pageNo' => [
            //     'rules'  => 'required|greater_than[' . PAGE_LENGTH . ']|numeric',
            //     'errors' => [
            //         'required'     => Lang('Language.required'),
            //         'greater_than' => Lang('Language.greater_than', [PAGE_LENGTH]),
            //         'numeric'      => Lang('Language.numeric', [$pageNo]),
            //     ]
            // ],
            'language' => [
                'rules'  => 'required|in_list[' . LANGUAGES . ']',
                'errors' => [
                    'required' => Lang('Language.required'),
                    'in_list'  => Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return $service->fail(
                [
                    'errors'  => $this->validator->getErrors(),
                    'message' => lang('Language.invalid_inputs')
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }

        try {
            // $currentPage = (!empty($pageNo)) ? $pageNo : 1;
            // $offset = ($currentPage - 1) * PER_PAGE;
            // $limit = PER_PAGE;

            $db = db_connect();
            $table = $db->table('tbl_package as p')->where('p.status', 'active')->where('status_by_admin', 'active')->where('p.is_featured', 'yes');

            $totalBuilder = clone $table;
            $total = $totalBuilder->countAllResults(false);

            $data = $table
                ->select('p.*')
                ->orderBy('p.id', 'DESC')
                // ->limit($limit, $offset)
                ->get()
                ->getResult(); // Fetch the paginated results

            return $service->success(
                [
                    'message' => Lang('Language.list_success'),
                    'data'    => $data
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'  => $e->getMessage(),
                    'message' => Lang('Language.fetch_list'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    // Search Ziyarat Package by Javeriya
    public function searchPackage() {
        $service = new Services();
        $service->cors();

        $rules = [
            'language' => [
                'rules'  => 'required|in_list[' . LANGUAGES . ']',
                'errors' => [
                    'required' => Lang('Language.required'),
                    'in_list'  => Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'search' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => Lang('Language.required'),
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return $service->fail(
                [
                    'errors'  => $this->validator->getErrors(),
                    'message' => lang('Language.invalid_inputs')
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }

        try {
            $search = $this->request->getVar('search');

            $db = db_connect();
            $table = $db->table('tbl_package as p')->where('p.status', 'active')->where('status_by_admin', 'active');

            if (isset($search) && !empty($search)) {
                $table->like('p.package_title', $search);
                // $table->orLike('p.package_details', $search);
                $table->orLike('p.city_loaction', $search);
                $table->orLike('p.pickup_loaction', $search);
                $table->orLike('p.drop_loaction', $search);
            }

            $totalBuilder = clone $table;
            $total = $totalBuilder->countAllResults(false);

            $data = $table
                ->select('p.*')
                ->orderBy('p.id', 'DESC')
                ->get()
                ->getResult(); // Fetch the paginated results

            return $service->success(
                [
                    'message' => Lang('Language.list_success'),
                    'data'    => [
                        'total'    => $total,
                        'packages' => $data,
                    ]
                ],
                ResponseInterface::HTTP_OK,
                $this->response
            );
        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'  => $e->getMessage(),
                    'message' => Lang('Language.fetch_list'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
