<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\TransportModel;

use Config\Services;
use Exception;

use App\Libraries\MailSender;

class Transport extends ResourceController
{
    public function index()
    {
      exit('No direct script access allowed.');
    }

    public function list()
    {
        $service   =  new Services();
        $transport   = new TransportModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('user_role');
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);
        if($checkToken)
        {
            try
            {
                $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
                $offset        = ( $currentPage - 1 ) * PER_PAGE;
                $limit         =  PER_PAGE;

                $whereCondition = '';

                if($user_role == 'admin'){ $whereCondition .= "e.status = '1'"; }

                // elseif($user_role == 'provider'){ $whereCondition .= "e.provider_id = ".$logged_user_id." AND e.status = '1'"; }

                elseif($user_role == 'user'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = '1'"; }

                $db = db_connect();
                $data = $db->table('tbl_transport_enquiry as e')
                    // ->join('tbl_provider as p','p.id = e.provider_id')
                    // ->join('tbl_meals as m','m.id = e.meals_id')
                    ->join('tbl_user as u','u.id = e.user_id')
                    ->select("e.*, CONCAT(u.firstname,' ',u.lastname) as user_name")
                    // ->where('e.status','1')
                    ->where($whereCondition)
                    // ->orderBy('e.id', 'DESC')
                    ->orderBy("CASE WHEN booking_status = 'pending' THEN 1 ELSE 2 END")
                    ->orderBy('created_date', 'DESC')      
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

    public function addEnquiry()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $transport   = new TransportModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

        $ota_id            =  $this->request->getVar('ota_id');
        $vehicle_type      =  $this->request->getVar('vehicle_type');
        $from_city         =  $this->request->getVar('from_city');
        $to_city           =  $this->request->getVar('to_city');
        $date              =  $this->request->getVar('date');
        $time              =  $this->request->getVar('time');

        $name              =  $this->request->getVar('name');
        $mobile            =  $this->request->getVar('mobile');

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
            'vehicle_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'from_city' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'to_city' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'time' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try {
                $data = array(
                    'user_id'       => $logged_user_id,
                    'ota_id'        => $ota_id,
                    'vehicle_type'  => $vehicle_type,
                    'from_city'     => $from_city,
                    'to_city'       => $to_city,
                    'date'          => $date,
                    'time'          => $time,
                    'name'          => (isset($name))?$name:'',
                    'mobile'        => (isset($mobile))?$mobile:'',
                    'created_date'  => date('Y-m-d H:i:s')
                );

                if($transport->insert($data)) 
                {
                    // PUSH NOTIFICATION
                    helper('notifications');
                    $db = db_connect();
                    $userinfo = $db->table('tbl_user')
                        ->select('*')
                        ->where('id', $_POST['logged_user_id'])
                        ->get()->getRow();

                    $title = "Transport Inquiry";
                    $message = "Your Inquiry has been sent. Thank you.";
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

    public function viewEnquiry()
    {
        $service   =  new Services();
        $transport   = new TransportModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
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

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

        if($checkToken)
        {
            try {
                    $db = db_connect();
                    $info = $db->table('tbl_transport_enquiry as e')
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
                        'message'   =>  Lang('Language.meal_not_found'),
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
