<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\MealsModel;
use App\Models\CuisionModel;
use App\Models\ProviderModel;
use App\Models\MealsMenuModel;
use App\Models\CheckoutModel;
use App\Models\MealsBookingModel;
use App\Models\AccountModel;
use App\Models\Admin_transaction_Model;
use App\Models\User_transaction_Model;

use App\Models\OtaProviderAccountModel;
use App\Models\ServiceCommisionModel;

use Config\Services;
use Exception;

use App\Libraries\MailSender;

use Stripe;
require 'vendor/autoload.php';

helper('auth');
helper('notifications');

class Meals extends ResourceController
{
    public function index()
    {
      exit('No direct script access allowed.');
    }

    // All meal lists for provider / admin / users
    public function allList()
    {   
        $service   =  new Services();
        $meals     = new MealsModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $search           =  $this->request->getVar('search');
        $meals_service    =  $this->request->getVar('meals_service');
        $cuisine_id       =  $this->request->getVar('cuisine_id');
        $provider_id      =  $this->request->getVar('provider_id');

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
            'user_role' => [
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

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;
        
        if($checkToken)
        {
            // try{
                if($user_role == 'provider'){
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
                }

                $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
                $offset        = ( $currentPage - 1 ) * PER_PAGE;
                $limit         =  PER_PAGE;

                $whereCondition = '';
                
                if(isset($cuisine_id) && $cuisine_id>0)
                {
                    $array=array_map('intval', explode(',', $cuisine_id));
                    $array = implode("','",$array);
                    $whereCondition .= "m.cuisine_id IN ('".$array."') AND ";
                }

                if(isset($meals_service) && $meals_service!=''){
                    $whereCondition .= "m.meals_service = '" . $meals_service . "' AND ";
                    // $array=array_map('intval', explode(',', $provider_id));
                    // $array = implode("','",$array);
                    // $whereCondition .= "m.provider_id IN ('".$array."') AND ";
                }

                if(isset($search) && $search!=''){
                    $whereCondition .= "m.title LIKE'%" . $search . "%' OR m.cities LIKE'%" . $search . "%' AND ";
                    // $whereCondition .= "m.cities LIKE'%" . $search . "%' AND ";
                    // $whereCondition .= "m.title LIKE'%" . $search . "%' AND ";
                }

                if(isset($provider_id) && $provider_id!=''){
                    $whereCondition .= "m.provider_id = '" . $provider_id . "%' AND ";
                }

                if($user_role == 'admin'){ $whereCondition .= "m.status = 'active'"; } 

                elseif($user_role == 'provider'){ $whereCondition .= "m.provider_id = ".$logged_user_id." AND m.status = 'active'"; }

                elseif($user_role == 'ota'){ $whereCondition .= "m.ota_id = ".$logged_user_id." AND m.status = 'active'"; }

                elseif($user_role == 'user'){ $whereCondition .= "m.status = 'active'"; }

                // By Query Builder
                $db = db_connect();
                $mealsData = $db->table('tbl_meals as m')
                    ->join('tbl_provider as p','p.id = m.provider_id')
                    ->join('tbl_cuision_master as c','c.id = m.cuisine_id')
                    ->select('m.*, p.firstname, p.lastname, c.name as cuisine_name')
                    ->where($whereCondition)
                    ->orderBy('m.id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()->getResult();

                $total =  $db->table('tbl_meals as m')->where($whereCondition)->countAllResults();

                return $service->success(
                    [
                        'message'       =>  Lang('Language.list_success'),
                        'data'          =>  [
                            'total'             =>  $total,
                            'mealsList'         =>  $mealsData,
                        ]
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );

            // } catch (Exception $e) {
            //     return $service->fail(
            //         [
            //             'errors'    =>  "",
            //             'message'   =>  Lang('Language.fetch_list'),
            //         ],
            //         ResponseInterface::HTTP_BAD_REQUEST,
            //         $this->response
            //     );
            // }
        }
        else {
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

    // Add Meals by Provider
    public function addMeals()
    {
        $service   =  new Services();
        $meals     = new MealsModel();
        $menuMeals = new MealsMenuModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $pageNo            =  $this->request->getVar('pageNo');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');
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
        $long              =  $this->request->getVar('lng');

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
            'user_role' => [
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
                        'provider_id'    => $logged_user_id,
                        'created_date'   => date('Y-m-d H:i:s'),
                        'provider_lat'   => $lat,
                        'provider_long'   => $long,
                    );
                    if($meals->insert($data)) 
                    {
                        // SAVE MULTIPLE IMAGE
                        $meals_id = $meals->insertID();
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
    
    // View Meals by Provider
    public function viewMeals()
    {
        $service   =  new Services();
        $meals     = new MealsModel();
        $menuMeals = new MealsMenuModel();
        $cuision   = new CuisionModel();
        
        $service->cors();
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $meals_id         =  $this->request->getVar('meals_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            // 'logged_user_id' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
            'user_role' => [
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

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

        if($checkToken)
        {
            try {
                 $menu_url = $menuMeals->select('menu_url')->where(['meals_id'=> $meals_id])->findAll();

                 $db = db_connect();
                 $isExist = $db->table('tbl_meals as m')
                    ->join('tbl_cuision_master as c','c.id = m.cuisine_id')
                    ->join('tbl_provider as p','p.id = m.provider_id')
                    ->select('m.*, c.name as cuisine_name, CONCAT(p.firstname, " ", p.lastname) as provider_name')
                    ->where('m.id', $meals_id)
                    ->get()->getRowArray();


                 $isExist['menu_url'] = $menu_url;

                 if(!empty($isExist))
                 {
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
                        'message'   =>  Lang('Language.fetch_list'),
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

    // Update Meals by Provider
    public function updateMeals()
    {
        $service        =  new Services();
        $meals          = new MealsModel();
        $ProviderModel  = new ProviderModel();
        $menuMeals      = new MealsMenuModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        // $pageNo            =  $this->request->getVar('pageNo');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');
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
            'user_role' => [
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
                        'provider_id'    => $logged_user_id,
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

    public function deleteMeals()
    {
        $service   =  new Services();
        $meals     = new MealsModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
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
            'user_role' => [
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

    public function getAllCuisions()
    {
        $service   =  new Services();
        $cuision     = new CuisionModel();
        $service->cors();
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

        if($checkToken)
        {
            try {
                    $cuisionAll = $cuision->where(['status'=>'1'])->findAll();
                    if(!empty($cuisionAll))
                    {
                        return $service->success([
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  $cuisionAll
                            ],
                            ResponseInterface::HTTP_OK,
                            $this->response
                        );
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.fetch_list'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

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

    // master for provider
    public function getAllProvider()
    {
        $service   =  new Services();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

        if($checkToken)
        {
            try {
                    $cuisionAll = $ProviderModel->where(['status'=>'active'])->findAll();
                    if(!empty($cuisionAll))
                    {
                        return $service->success([
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  $cuisionAll
                            ],
                            ResponseInterface::HTTP_OK,
                            $this->response
                        );
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.fetch_list'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

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

    // MEALS CHECKOUT - 06 SEP 2022
    public function mealsCheckOut()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $CheckoutModel = new CheckoutModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $price          =  $this->request->getVar("price");
        $meals_name     =  $this->request->getVar("meals_name");
        $service_type   =  $this->request->getPost("service_type");
		$meals_id       =  $this->request->getPost("meals_id");
        $fullname       = $this->request->getPost("fullname");
		$contact_no     = $this->request->getPost("contact_no");
		$email          = $this->request->getPost("email");

        $ota_name       = $this->request->getVar("ota_name");

        $rules = [
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'user_role' => [
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
            'meals_name' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'service_type' => [
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
            'fullname' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'contact_no' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'email' => [
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
                    $stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);

                    // $session = \Stripe\Checkout\Session::create([
                    //     'line_items' => [[
                    //         'price_data' => [
                    //             'currency' => 'SAR',
                    //             'product_data' => [
                    //                 'name' => $meals_name,
                    //             ],
                    //             'unit_amount' => $price * 100,
                    //         ],
                    //         'quantity' => 1,
                    //     ]],
            
                    //     'mode' => 'payment',
                    //     'success_url' => 'https://umrahaddons.com/"'.$ota_name.'"/meals/success',
                    //     'cancel_url' => 'https://umrahaddons.com/"'.$ota_name.'"/meals/failure',
                    // ]);

                    $session = \Stripe\Checkout\Session::create([
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'SAR',
                                'product_data' => [
                                    'name' => $meals_name,
                                ],
                                'unit_amount' => $price * 100,
                            ],
                            'quantity' => 1,
                        ]],
            
                        'mode' => 'payment',
                        'success_url' => 'https://umrahaddons.com/meals/success',
                        'cancel_url' => 'https://umrahaddons.com/meals/failure',
                    ]);

                    $data = [
                        'session_id' => $session->id,
                        'object' => $session->object,
                        'amount_total' => $session->amount_total,
                        'currency' => $session->currency,
                        // 'customer_email'=>$session->customer_email,
                        'payment_intent' => $session->payment_intent,
                        'payment_status' => $session->payment_status,
                        'url' => $session->url,
                        // 'customer_details'=>$session->customer_details,
                        'user_id' => $logged_user_id,
                        'user_role' => $user_role,
                        'ota_id' => '1',
                        'service_id' => $meals_id,
                        'service_type' => $service_type,
                        'status' => 'active',
                        'guest_fullname' => $fullname,
                        'guest_contact_no' => $contact_no,
                        'guest_email' => $email
                    ];

                    if ($CheckoutModel->insert($data)) {
                        return $service->success([
                            'message'       =>  Lang('Language.Session CheckOut Given'),
                            'data'          =>  $session
                            ],
                            ResponseInterface::HTTP_CREATED,
                            $this->response
                        );
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.Payment Failed'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
                
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.some_things_error'),
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

    public function mealsSuccessPayment()
    {
        // echo "YES"; exit;
        $service      =  new Services();
        $bookingModel = new MealsBookingModel();
        $CheckoutModel = new CheckoutModel();
        $meals     = new MealsModel();
        $ProviderModel  = new ProviderModel();
        $AccountModel = new AccountModel();
        $Admin_transaction_Model = new Admin_transaction_Model();
		$User_transaction_Model = new User_transaction_Model();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
        $ServiceCommisionModel = new ServiceCommisionModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $session_id       = $this->request->getVar("session_id");
		$meals_id         = $this->request->getVar("meals_id");
		$ota_id           = $this->request->getVar("ota_id");
		$service_type     = "meals";
		$active           = "active";

		$no_of_person     = $this->request->getVar('no_of_person');
		$start_date       = $this->request->getVar('start_date');
		$end_date         = $this->request->getVar('end_date');
		$meals_type       = $this->request->getVar('meals_type');
		$meals_service    = $this->request->getVar('meals_service');
		$city             = $this->request->getVar('city');
		$address          = $this->request->getVar('address');
		$cost_per_day_person    = $this->request->getVar('cost_per_day_person');
		$no_of_days       = $this->request->getVar('no_of_days');
		$total_cost       = $this->request->getVar('total_cost');
		$notes            = $this->request->getVar('notes');
		$created_date     = $this->request->getVar('created_date');

        $rules = [
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'user_role' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'session_id' => [
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
            'no_of_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'start_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'end_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'meals_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'city' => [
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
            'cost_per_day_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'no_of_days' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'total_cost' => [
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

        if($checkToken){

            try{

                $checkOutData = $CheckoutModel->where('session_id', $session_id)->first();
               
                if (empty($checkOutData)) 
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Checkout Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $mealsData = $meals->where('id', $meals_id)->first();
                if (empty($mealsData)) 
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.meal_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $checkoutid  = $checkOutData['id'];
                $provider_id = $mealsData['provider_id'];

                $rate = $mealsData['cost_per_day'];

                // admin commission 
                $provider_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $provider_id)->where('user_role', 'provider')->first();
                $admin_commision_per = $provider_commision_data['commision_in_percent'];
                $admin_percent = $admin_commision_per / 100;
                $admin_amount = $admin_percent * $rate;

                // ota  commission
                $ota_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $ota_id)->where('user_role', 'ota')->first();
                $ota_commision = $ota_commision_data['commision_in_percent'];
                $ota_precent = $ota_commision / 100;
                $ota_ammount = $ota_precent * $rate;

                // provider amount
                $provider_amount = $rate - $admin_amount;

                // admin remain apmount
                $remaining_admin_comm_amount = $admin_amount - $ota_ammount;

                $stripe = new \Stripe\StripeClient(
                    STRIPE_SECRET
                );

                $stripe_session_data = $stripe->checkout->sessions->retrieve(
                    $session_id,
                    []
                );

                if(!empty($stripe_session_data)){
                    $inprocessbooking = [
                        'provider_id' => $mealsData['provider_id'],
                        'meals_id' => $meals_id,
                        'user_id' => $logged_user_id,
                        'ota_id' => $ota_id,
                        'full_name' => $checkOutData['guest_fullname'],
                        'mobile' => $checkOutData['guest_contact_no'],
                        'no_of_person' => $no_of_person,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'meals_type' => $meals_type,
                        'meals_service' => $meals_service,
                        'city' => $city,
                        'address' => $address,
                        'cost_per_day_person' => $cost_per_day_person,
                        'no_of_days' => $no_of_days,
                        'total_cost' => $total_cost,
                        'notes' => $notes,
                        'booking_status_user' => 'in-progress',
                        'booking_status_stripe' => 'open',
                        'payment_status' => 'pending',
                        'ota_commision' => $ota_commision,
                        'provider_commision' => $admin_commision_per,
                        'total_admin_comm_amount' => $admin_amount,
                        'remaining_admin_comm_amount' => $remaining_admin_comm_amount,
                        'ota_commision_amount' => $ota_ammount,
                        'provider_amount' => $provider_amount,
                        'ota_payment_status' => 'pending',
                        'provider_payment_status' => 'pending',
                        'session_id' => $session_id,
                        'checkout_id' => $checkoutid,
                        'remianing_amount_pay' => $total_cost - 49,
                    ];

                    if ($bookingModel->insert($inprocessbooking)) {

                        $lastbooking_id = $bookingModel->insertID;
                        
                        if ($stripe_session_data['payment_status'] == 'paid') {    
                        // if (1) {    
                            
                            $confirm_booking = [
                                'booking_status_user' => 'confirm',
                                'booking_status_stripe' => $stripe_session_data->status,
                                'payment_status' => 'completed'
                            ];

                            $update_Booking = $bookingModel->update($lastbooking_id, $confirm_booking);
        
                            // SEND EAMIL TO PROVIDER on PAckage Booking
                            $Providerdata = $ProviderModel->where("id", $provider_id)->first();
                            $providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];
        
                            $data = array('user_role' => 'provider','user_name' => $checkOutData['guest_fullname'], 'provider_name' => $providerFullname, 'package_name'=>$mealsData['title']);
                            $msg_template = view('emmail_templates/package_booking.php', $data);
                            $subject      = 'Package Booked';
                            $to_email     =  $Providerdata['email']; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);        
                            // SEND EAMIL TO USER on PAckage Booking
                            $data = array('user_role' => 'user','user_name' => $checkOutData['guest_fullname'], 'provider_name' => $providerFullname, 'package_name'=>$mealsData['title']);
                            $msg_template = view('emmail_templates/package_booking.php', $data);
                            $subject      = 'Package Booked';
                            $to_email     =  $checkOutData['guest_email']; // user email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                            // EnD
        
                            // for  provider 
                            $providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $mealsData['provider_id'])->first();

                            if (empty($providerAccount)) {
                                $provider_account = [
                                    'user_role' => 'provider',
                                    'user_id' => $provider_id,
                                    'total_amount' => $provider_amount,
                                    'pending_amount' => $provider_amount,
                                    'withdrawal_amount' => '00',
                                ];
                                $OtaProviderAccountModel->insert($provider_account);
                            } else {
                                $provider_account_id = $providerAccount['id'];
                                $pervious_total_amount = $providerAccount['total_amount'];
                                $pervious_pending_amount = $providerAccount['pending_amount'];
                                $update_provier_amount = [
                                    'total_amount' => $pervious_total_amount + $provider_amount,
                                    'pending_amount' => $pervious_pending_amount + $provider_amount,
                                ];
                                $OtaProviderAccountModel->update($provider_account_id, $update_provier_amount);
                            }
                            
        
                            // for ota 
                            $ota_data = $OtaProviderAccountModel->where('user_role', 'ota')->where('user_id', $ota_id)->first();

                            if (empty($ota_data)) {
                                $ota_account = [
                                    'user_role' => 'ota',
                                    'user_id' => $ota_id,
                                    'total_amount' => $ota_ammount,
                                    'pending_amount' => $ota_ammount,
                                    'withdrawal_amount' => '00',
                                ];
                                $OtaProviderAccountModel->insert($ota_account);
                            } else {
                                $ota_account_id = $ota_data['id'];
                                $pervious_total_amount = $ota_data['total_amount'];
                                $pervious_pending_amount = $ota_data['pending_amount'];
                                $update_ota_amount = [
                                    'total_amount' => $pervious_total_amount + $provider_amount,
                                    'pending_amount' => $pervious_pending_amount + $provider_amount,
                                ];
                                $OtaProviderAccountModel->update($ota_account_id, $update_ota_amount);
                            }
                            
                            $admin_account_data = $AccountModel->where('id', '1')->first();
                            $old_balance = $admin_account_data['amount'];
                            
                            // admin transaction data
                            $admin_transaction = [
                                'admin_id' => '1',
                                'user_id' => $logged_user_id,
                                'user_type' => 'user',
                                'transaction_type' => 'Cr',
                                'service_type' => $service_type,
                                'service_id' => $meals_id,
                                'transaction_reason' => "Credit Amount of " . $service_type . " Of User",
                                'currency_code' => 'SAR',
                                'account_id' => 1,
                                'old_balance' => $old_balance,
                                'transaction_amount' => $total_cost,
                                'current_balance' => $old_balance + $total_cost,
                                'transaction_id' => generateRandomString('TRANSACTION'),
                                'transaction_status' => 'success',
                                'transaction_date' => date("Y-m-d"),
                                'payment_method' => 'STRIPE',
                                'booking_id'  => $lastbooking_id,
                                'payment_session_id' => $session_id
                            ];
                            $transaction_id = $admin_transaction['transaction_id'];
                            $Admin_transaction_Model->insert($admin_transaction);
        
                            // user transaction
                            $admin_account = [
                                'amount' => $old_balance + $total_cost
                            ];
                            $AccountModel->update('1', $admin_account);

                            $user_transaction = [
                                'customer_id' => $logged_user_id,
                                'user_id' => '1',
                                'user_type' => 'admin',
                                'transaction_type' => 'Dr',
                                'transaction_reason' => 'Meals Amount to Admin',
                                'currency_code' => 'SAR',
                                'transaction_amount' => $total_cost,
                                'transaction_id' => $transaction_id,
                                'transaction_status' => 'success',
                                'transaction_date' => date("Y-m-d"),
                                'service_type' => $service_type,
                                'service_id' => $meals_id,
                                'payment_method' => 'STRIPE'
                            ];
                            $User_transaction_Model->insert($user_transaction);

                            // PUSH NOTIFICATION
                            helper('notifications');
                            $db = db_connect();
                            $userinfo = $db->table('tbl_user')
                                ->select('*')
                                ->where('id', $_POST['logged_user_id'])
                                ->get()->getRowArray();

                            $title = "Meals Booking";
                            $message = "Your booking has been confirmed. Thank you.";

                            $fmc_ids = array($userinfo['device_token']);
                            
                            $notification = array(
                                'title' => $title ,
                                'message' => $message,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                                'date' => date('Y-m-d H:i'),
                            );
                            // echo json_encode($notification);exit;

                            if($userinfo['device_type']!='web'){ sendFCMMessage($notification, $fmc_ids); }

                            // PROVIDER NOTIFICATION
                            $providerinfo = $db->table('tbl_provider')
                                ->select('*')
                                ->where('id', $provider_id)
                                ->get()->getRow();
                        
                            $title = "Meals Booking";
                            $message = "Meals Booking recevied from a ".$checkOutData['guest_fullname']." for a ".$mealsData['title'];
                            $fmc_ids = array($providerinfo->device_token);
                            $notification = array(
                                'title' => $title ,
                                'message' => $message,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                                'date' => date('Y-m-d H:i'),
                            );
                            if($providerinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }
                            // EnD

                            return $service->success([
                                    'message'       =>  Lang('Language.Payment Accepted'),
                                    'data'          =>  ""
                                ],
                                ResponseInterface::HTTP_CREATED,
                                $this->response
                            );

                        } else {
                            return $service->fail(
                                [
                                    'errors'    =>  "",
                                    'message'   =>  Lang('Language.Transaction  Failed'),
                                ],
                                ResponseInterface::HTTP_BAD_REQUEST,
                                $this->response
                            );
                        }
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.Booking Failed'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

                } else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Transaction Failed'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Transaction  Failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }
        } 
        else {
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

    // COD PAYMENT - 14 OCT 2022 - RIZ
    public function mealsCodBooking()
    {
        // echo "YES"; exit;
        $service      =  new Services();
        $bookingModel = new MealsBookingModel();
        $CheckoutModel = new CheckoutModel();
        $meals     = new MealsModel();
        $AccountModel = new AccountModel();
        $Admin_transaction_Model = new Admin_transaction_Model();
		$User_transaction_Model = new User_transaction_Model();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
        $ServiceCommisionModel = new ServiceCommisionModel();
        $ProviderModel = new ProviderModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $meals_id         = $this->request->getVar("meals_id");
        $ota_id           = $this->request->getVar("ota_id");
        $service_type     = "meals";
        $active           = "active";

        $no_of_person     = $this->request->getVar('no_of_person');
        $start_date       = $this->request->getVar('start_date');
        $end_date         = $this->request->getVar('end_date');
        $meals_type       = $this->request->getVar('meals_type');
        $meals_service    = $this->request->getVar('meals_service');
        $city             = $this->request->getVar('city');
        $lat              = $this->request->getVar('lat');
        $long             = $this->request->getVar('long');
        $address          = $this->request->getVar('address');
        $cost_per_day_person    = $this->request->getVar('cost_per_day_person');
        $no_of_days       = $this->request->getVar('no_of_days');
        $total_cost       = $this->request->getVar('total_cost');
        $notes            = $this->request->getVar('notes');

        $full_name         = $this->request->getVar('full_name');
		$contact_no        = $this->request->getVar('contact_no');
		$email             = $this->request->getVar('email');

        $rules = [
            'logged_user_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'user_role' => [
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
            'no_of_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'start_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'end_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'meals_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'city' => [
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
            'cost_per_day_person' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'no_of_days' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'total_cost' => [
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

        if($checkToken ){

            try 
            {
                $mealsData = $meals->where('id', $meals_id)->first();
                if (empty($mealsData)) 
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.meal_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $provider_id = $mealsData['provider_id'];
                $rate = $total_cost;

                // admin commission 
                $provider_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $provider_id)->where('user_role', 'provider')->first();
                if(!empty($provider_commision_data)){
                    $admin_commision_per = $provider_commision_data['commision_in_percent'];
                    $admin_percent = $admin_commision_per / 100;
                    $admin_amount = $admin_percent * $rate;
                } else { $admin_amount = 0;}

                // ota  commission
                $ota_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $ota_id)->where('user_role', 'ota')->first();
                if(!empty($ota_commision_data)){
                    $ota_commision = $ota_commision_data['commision_in_percent'];
                    $ota_precent = $ota_commision / 100;
                    $ota_ammount = $ota_precent * $rate;
                }

                $provider_amount = $rate - $admin_amount;

                $remaining_admin_comm_amount = $admin_amount - $ota_ammount;

                $inprocessbooking = [
                    'provider_id' => $mealsData['provider_id'],
                    'meals_id' => $meals_id,
                    'user_id' => $logged_user_id,
                    'ota_id' => $ota_id,
                    'full_name' => $full_name,
                    'mobile' => $contact_no,
                    'no_of_person' => $no_of_person,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'meals_type' => $meals_type,
                    'meals_service' => $meals_service,
                    'city' => $city,
                    'lat'  => $lat ? $lat : '',
                    'long'  => $long ? $long : '',
                    'address' => $address,
                    'cost_per_day_person' => $cost_per_day_person,
                    'no_of_days' => $no_of_days,
                    'total_cost' => $total_cost,
                    'notes' => $notes,
                    'booking_status_user' => 'confirm',
                    'booking_status_stripe' => 'completed',
                    'payment_status' => 'completed',
                    'ota_commision' => $ota_commision,
                    'provider_commision' => $admin_commision_per,
                    'total_admin_comm_amount' => $admin_amount,
                    'remaining_admin_comm_amount' => $remaining_admin_comm_amount,
                    'ota_commision_amount' => $ota_ammount,
                    'provider_amount' => $provider_amount,
                    'ota_payment_status' => 'pending',
                    'provider_payment_status' => 'pending',
                    'session_id' => '',
                    'checkout_id' => 'COD',
                    'remianing_amount_pay' => 0,
                    'created_date' => date('Y-m-d H:i:s'),
                ];

                if ($bookingModel->insert($inprocessbooking)) {

                    $lastbooking_id = $bookingModel->insertID;

                    // SEND EAMIL TO PROVIDER on PAckage Booking
                    $Providerdata = $ProviderModel->where("id", $provider_id)->first();
                    $providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];

                    $data = array('user_role' => 'provider','user_name' => $full_name, 'provider_name' => $providerFullname, 'package_name'=>$mealsData['title']);
                    $msg_template = view('emmail_templates/package_booking.php', $data);
                    $subject      = 'Meals Booked';
                    $to_email     =  $Providerdata['email']; // provider email
                    $filename = "";
                    $send     = sendEmail($to_email, $subject, $msg_template,$filename);
                    // SEND EAMIL TO USER on PAckage Booking
                    $data = array('user_role' => 'user','user_name' => $full_name, 'provider_name' => $providerFullname, 'package_name'=>$mealsData['title']);
                    $msg_template = view('emmail_templates/package_booking.php', $data);
                    $subject      = 'Meals Booked';
                    $to_email     =  $email; // user email
                    $filename = "";
                    $send     = sendEmail($to_email, $subject, $msg_template,$filename);                    // EnD

                    // PUSH NOTIFICATION
                    helper('notifications');
                    $db = db_connect();
                    $userinfo = $db->table('tbl_user')
                        ->select('*')
                        ->where('id', $_POST['logged_user_id'])
                        ->get()->getRow();

                    $title = "Meals Booking";
                    $message = "Your booking has been confirmed. Thank you.";
                    $fmc_ids = array($userinfo->device_token);
                    
                    $notification = array(
                        'title' => $title ,
                        'message' => $message,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                        'date' => date('Y-m-d H:i'),
                    );
                    if($userinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }

                    // PROVIDER NOTIFICATION
                    $providerinfo = $db->table('tbl_provider')
                        ->select('*')
                        ->where('id', $provider_id)
                        ->get()->getRow();
                
                    $title = "Meals Booking";
                    $message = "Meals Booking recevied from a ".$full_name." for a ".$mealsData['title'];
                    $fmc_ids = array($providerinfo->device_token);
                    $notification = array(
                        'title' => $title ,
                        'message' => $message,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                        'date' => date('Y-m-d H:i'),
                    );
                    if($providerinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }
                    // EnD

                    // for  provider 
                    $providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $mealsData['provider_id'])->first();
                    if (empty($providerAccount)) {
                        $provider_account = [
                            'user_role' => 'provider',
                            'user_id' => $provider_id,
                            'total_amount' => $provider_amount,
                            'pending_amount' => $provider_amount,
                            'withdrawal_amount' => '00',
                        ];
                        $OtaProviderAccountModel->insert($provider_account);
                    } else {
                        $provider_account_id = $providerAccount['id'];
                        $pervious_total_amount = $providerAccount['total_amount'];
                        $pervious_pending_amount = $providerAccount['pending_amount'];
                        $update_provier_amount = [
                            'total_amount' => $pervious_total_amount + $provider_amount,
                            'pending_amount' => $pervious_pending_amount + $provider_amount,
                        ];
                        $OtaProviderAccountModel->update($provider_account_id, $update_provier_amount);
                    }

                    // for ota 
                    $ota_data = $OtaProviderAccountModel->where('user_role', 'ota')->where('user_id', $ota_id)->first();
                    if (empty($ota_data)) {
                        $ota_account = [
                            'user_role' => 'ota',
                            'user_id' => $ota_id,
                            'total_amount' => $ota_ammount,
                            'pending_amount' => $ota_ammount,
                            'withdrawal_amount' => '00',
                        ];
                        $OtaProviderAccountModel->insert($ota_account);
                    } else {
                        $ota_account_id = $ota_data['id'];
                        $pervious_total_amount = $ota_data['total_amount'];
                        $pervious_pending_amount = $ota_data['pending_amount'];
                        $update_ota_amount = [
                            'total_amount' => $pervious_total_amount + $provider_amount,
                            'pending_amount' => $pervious_pending_amount + $provider_amount,
                        ];
                        $OtaProviderAccountModel->update($ota_account_id, $update_ota_amount);
                    }

                    $admin_account_data = $AccountModel->where('id', '1')->first();
                    $old_balance = $admin_account_data['amount'];

                    // admin transaction data
                    $admin_transaction = [
                        'admin_id' => '1',
                        'user_id' => $logged_user_id,
                        'user_type' => 'user',
                        'transaction_type' => 'Cr',
                        'service_type' => $service_type,
                        'service_id' => $meals_id,
                        'transaction_reason' => "Credit Amount of " . $service_type . " Of User",
                        'currency_code' => 'SAR',
                        'account_id' => 1,
                        'old_balance' => $old_balance,
                        'transaction_amount' => $total_cost,
                        'current_balance' => $old_balance + $total_cost,
                        'transaction_id' => generateRandomString('TRANSACTION'),
                        'transaction_status' => 'success',
                        'transaction_date' => date("Y-m-d"),
                        'payment_method' => 'COD',
                        'booking_id'  => $lastbooking_id,
                        'payment_session_id' => ''
                    ];
                    $transaction_id = $admin_transaction['transaction_id'];
                    $Admin_transaction_Model->insert($admin_transaction);

                    // user transaction
                    $admin_account = [
                        'amount' => $old_balance + $total_cost
                    ];
                    $AccountModel->update('1', $admin_account);

                    $user_transaction = [
                        'customer_id' => $logged_user_id,
                        'user_id' => '1',
                        'user_type' => 'admin',
                        'transaction_type' => 'Dr',
                        'transaction_reason' => 'Meals Amount to Admin',
                        'currency_code' => 'SAR',
                        'transaction_amount' => $total_cost,
                        'transaction_id' => $transaction_id,
                        'transaction_status' => 'success',
                        'transaction_date' => date("Y-m-d"),
                        'service_type' => $service_type,
                        'service_id' => $meals_id,
                        'payment_method' => 'COD'
                    ];
                    $User_transaction_Model->insert($user_transaction);

                    return $service->success([
                        'message'       =>  Lang('Language.Payment Accepted'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );

                }  else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Booking Failed'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                echo json_encode($inprocessbooking); exit;
                
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Transaction  Failed'),
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
  
}
