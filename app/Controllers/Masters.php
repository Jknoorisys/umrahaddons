<?php

namespace App\Controllers;

use App\Models\DepartureCityMaster;
use App\Models\PackageModels;
use App\Models\ZiyaratPoints;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use Config\Services;
use Exception;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Masters extends ResourceController
{
    public function index()
    {
        exit('No direct script access allowed.');
    }

    public function packageDuration()
    {
       $service   =  new Services();
       $service->cors();

       $token            =  $this->request->getVar('token');
       $user_role        =  $this->request->getVar('user_role');
       $logged_user_id   =  $this->request->getVar('logged_user_id');

       $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

       if($checkToken)
        {
            try {
                    $db = db_connect();
                    $packageDuration = $db->table('tbl_package_duration')
                        ->select('id, duration')
                        ->where('status','1')
                        ->get()->getResult();
                    if(!empty($packageDuration))
                    {
                        return $service->success([
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  $packageDuration
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

    public function UserAppVersion() 
    {
        $service   =  new Services();
        $service->cors();

        $app_ver = $this->request->getVar('version');
        $device_type = $this->request->getVar('device_type'); // android/ios
        $app_name = $this->request->getVar('app_name'); // android/ios

        try {
                $db = db_connect();
                $app_version = $db->table('tbl_users_app_version')->select('*')->where('app_name',$app_name)->get()->getRow();
                if ($device_type == 'android') {
                    if($app_ver < $app_version->app_version_android) {
                        if($app_version->forcefully_update_android == 1) 
                        {
                            return $service->success([
                                'message'       =>  Lang('Language.New application version is available, please update to continue.'),
                                'data'          =>  ['forcefullyUpdate' => 1],
                            ],
                                ResponseInterface::HTTP_OK,
                                $this->response
                            );
                        } else { 
                                return $service->success([
                                'message'       =>  Lang('Language.New application version is available, please update to continue.'),
                                'data'          =>  ['forcefullyUpdate' => 0],
                            ],
                                ResponseInterface::HTTP_OK,
                                $this->response
                            ); 
                        }
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  "You have latest version of application.",
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
                } else {
                    if($app_ver < $app_version->app_version_ios) {
                        if($app_version->forcefully_update_ios == 1) 
                        {
                            return $service->success([
                                'message'       =>  Lang('Language.New application version is available, please update to continue.'),
                                'data'          =>  ['forcefullyUpdate' => 1],
                            ],
                                ResponseInterface::HTTP_OK,
                                $this->response
                            );
                        } else { 
                                return $service->success([
                                'message'       =>  Lang('Language.New application version is available, please update to continue.'),
                                'data'          =>  ['forcefullyUpdate' => 0],
                            ],
                                ResponseInterface::HTTP_OK,
                                $this->response
                            ); 
                        }
                    } else {
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  "You have latest version of application.",
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
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
    }

    public function checkProfileStatus()
    {
        $service         =  new Services();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken )
        {
            try{

                $table = ($user_role == 'guide') ? 'tbl_guide' : 'tbl_provider';
                $db = db_connect();
                $info = $db->table($table.' as t')
                ->select('t.*')
                ->where('t.id',$logged_user_id)
                ->get()->getRow();

                if($info->status != 'active'){
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  "failed",
                        ],
                        ResponseInterface::HTTP_OK,
                        $this->response
                    );
                } else {
                    return $service->success(
                        [
                            'message'       =>  "",
                            'data'          =>  ['is_verify' => $info->is_verify]
                        ],
                        ResponseInterface::HTTP_OK,
                        $this->response
                    );
                }
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  "failed",
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

    public function testFCM()
    {
        // PUSH NOTIFICATION
        // $userInfo = $this->db->query("SELECT u.* FROM tbl_users as u JOIN tbl_pass as p ON p.user_id = u.id where p.id =".$pass_id."")->row();
        helper('notifications');
        $title = "Package Booking";
        $message = "Congratulations! The booking has been confirmed.";
        $fmc_ids = array('dntAgCeETE-d3rin4YPMtr:APA91bFdBV7XhfKP_ClwduYBasfjp1-ODmTWDPc44bED1QzKpf1Roxxp3A-PuOdS8YSZ-ZgfVt6aaVIVqSum8CVC2ic10FnFSIvCJl_Hp83ai-ln-4LCHkIA80XhH22ea7nZ0A-lRMmB');
        $notification = array(
            'title' => $title ,
            'message' => $message,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
            'date' => date('Y-m-d H:i'),
        );
        echo json_encode(sendFCMMessage($notification, $fmc_ids)); exit;
        // EnD
    }

    public function langauge()
    {
       $service   =  new Services();
       $service->cors();

       $token            =  $this->request->getVar('token');
       $user_role        =  $this->request->getVar('user_role');
       $logged_user_id   =  $this->request->getVar('logged_user_id');

       $checkToken = ($user_role!='guide')?$service->getAccessForSignedUser($token, $user_role):true;

       if($checkToken)
        {
            try {
                    $db = db_connect();
                    $language = $db->table('languages')
                        ->select('id, name')
                        ->where('status','1')
                        ->get()->getResult();
                    if(!empty($language))
                    {
                        return $service->success([
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  $language
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

    public function checkMail()
    {
        $service   =  new Services();
        $service->cors();
 
        $user_role        =  $this->request->getVar('user_role');
        $email            =  $this->request->getVar('email');
 
        if(true)
         {
             try {
                     $db = db_connect();
                     $table = 'tbl_'.$user_role;
                     $info = $db->table($table)
                         ->select('*')
                         ->where('email', $email)
                         ->get()->getResult();

                     if(empty($info))
                     {
                         return $service->success([
                             'message'       =>  '',
                             'data'          =>  '',
                             ],
                             ResponseInterface::HTTP_OK,
                             $this->response
                         );
                     } else {
                         return $service->fail(
                             [
                                 'errors'    =>  "",
                                 'message'   =>  Lang('Language.User Already Exists'),
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

    public function fullPackageInclusions()
    {
       $service   =  new Services();
       $service->cors();

       
        try {
                $db = db_connect();
                $inclusions = $db->table('tbl_full_package_inclusions')
                    ->select('id, name, "false" as checked')
                    ->get()->getResult();
                if(!empty($inclusions))
                {
                    foreach ($inclusions as $key) {
                        $key->checked = FALSE;
                    }
                    return $service->success([
                        'message'       =>  Lang('Language.list_success'),
                        'data'          =>  $inclusions
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
    }

    // ziyarat package master api
    public function ziyaratPackageMaster()
    {
        $service   =  new Services();
        $package     = new PackageModels();
      
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

       
        try {
            
            $packages = $package->where(['status' => 'active', 'status_by_admin' => 'active'])->select('tbl_package.id, tbl_package.package_title')->orderBy('package_title')->get()->getResult();
            if(!empty($packages))
            {
                return $service->success([
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  $packages
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.list_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.list_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    // ziyarat points master api
    public function ziyaratPointsMaster()
    {
        $service   =  new Services();
        $point     = new ZiyaratPoints();
      
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

       
        try {
            
            $points = $point->where('status', '1')->select('id, title_en, name_en')->orderBy('name_en')->get()->getResult();
            if(!empty($points))
            {
                return $service->success([
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  $points
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.list_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.list_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }

    // departure city master api
    public function departureCityMaster()
    {

        $service   =  new Services();
        $city     = new DepartureCityMaster();
      
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
       
        try {
            
            $cities = $city->where('status', '1')->select('*')->orderBy('id')->get()->getResult();
            if(!empty($cities))
            {
                return $service->success([
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  $cities
                    ],
                    ResponseInterface::HTTP_OK,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.list_failed'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  "",
                    'message'   =>  Lang('Language.list_failed'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
