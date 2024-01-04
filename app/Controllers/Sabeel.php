<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\SabeelModel;
use App\Models\ProviderModel;
use App\Models\CheckoutModel;
use App\Models\SabeelBookingModel;

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

class Sabeel extends BaseController
{
    public function index()
    {
      exit('No direct script access allowed.');
    }

    // All sabeel lists for provider / admin / users
    public function allList()
    {   
        $service           =  new Services();
        $sabeel            = new SabeelModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

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
            try{
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

                $whereCondition = "";

                if(isset($search) && $search!=''){
                    $whereCondition .= "m.name LIKE'%" . $search . "%' AND ";
                }

                if($user_role == 'admin'){ $whereCondition .= "m.status = '1'"; } 

                if($user_role == 'user'){ $whereCondition .= "m.status = '1'"; } 

                if($user_role == 'provider'){ $whereCondition .= "m.provider_id = ".$logged_user_id." AND m.status = '1' "; }

                // By Query Builder
                $db = db_connect();
                $sabeelData = $db->table('tbl_sabeel as m')
                    ->join('tbl_provider as p','p.id = m.provider_id')
                    ->select('m.*, p.firstname, p.lastname')
                    ->where($whereCondition)
                    ->orderBy('m.id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()->getResult();

                $total =  $db->table('tbl_sabeel as m')->where($whereCondition)->countAllResults();

                return $service->success(
                    [
                        'message'       =>  Lang('Language.list_success'),
                        'data'          =>  [
                            'total'             =>  $total,
                            'sabeelList'         =>  $sabeelData,
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

    // Add Sabeel by Provider
    public function addSabeel()
    {
        $service        =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

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
                        'provider_id'    =>    $logged_user_id,
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

    // View Sabeel by Provider
    public function viewSabeel()
    {
        $service   =  new Services();
        $sabeel         = new SabeelModel();
        $service->cors();
        
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $sabeel_id        =  $this->request->getVar('sabeel_id');

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

        $checkToken = ($user_role!='user')?$service->getAccessForSignedUser($token, $user_role):true;

        if($checkToken)
        {
            try {
            
            $isExist = $sabeel->find($sabeel_id);
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

    // Update Sabeel by Provider
    public function updateSabeel()
    {
        $service        =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

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

    public function deleteSabeel()
    {
        $service   =  new Services();
        $sabeel         = new SabeelModel();
        $ProviderModel     = new ProviderModel();
        $service->cors();
        
        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $sabeel_id         =  $this->request->getVar('sabeel_id');

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

    // SABEEL CHECKOUT - 07 OCT 2022
    public function sabeelCheckOut()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $CheckoutModel = new CheckoutModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $price            =  $this->request->getVar("price");
        $sabeel_name      =  $this->request->getVar("sabeel_name");
        $service_type     =  $this->request->getPost("service_type");
		$sabeel_id        =  $this->request->getPost("sabeel_id");

        $fullname       = $this->request->getPost("fullname");
		$contact_no     = $this->request->getPost("contact_no");
		$email          = $this->request->getPost("email");

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
            'sabeel_name' => [
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
            'sabeel_id' => [
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
            // try {
                    $stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);

                    $session = \Stripe\Checkout\Session::create([
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'SAR',
                                'product_data' => [
                                    'name' => $sabeel_name,
                                ],
                                'unit_amount' => $price * 100,
                            ],
                            'quantity' => 1,
                        ]],
            
                        'mode' => 'payment',
                        'success_url' => 'https://nooricoders.click/ua/sabeel/success',
                        'cancel_url' => 'https://nooricoders.click/ua/sabeel/failure',
                    ]);

                    // echo json_encode($session); exit;

                    $data = [
                        'session_id' => $session->id,
                        'object' => $session->object,
                        'amount_total' => $session->amount_total,
                        'currency' => $session->currency,
                        // 'customer_email'=>$session->customer_email,
                        'payment_intent' => $session->payment_intent,
                        'payment_status' => 'unpaid',
                        // 'url' => $session->charges['url'],
                        'url' => $session->url,
                        // 'customer_details'=>$session->customer_details,
                        'user_id' => $logged_user_id,
                        'user_role' => $user_role,
                        // 'ota_id' => '1',
                        'service_id' => $sabeel_id,
                        'service_type' => $service_type,
                        'status' => 'active',
                        'guest_fullname' => $fullname,
                        'guest_contact_no' => $contact_no,
                        'guest_email' => $email
                    ];

                    // echo json_encode($data); exit;

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
                
            // } catch (Exception $e) {
            //     return $service->fail(
            //         [
            //             'errors'    =>  "",
            //             'message'   =>  Lang('Language.some_things_error'),
            //         ],
            //         ResponseInterface::HTTP_BAD_REQUEST,
            //         $this->response
            //     );
            // }

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

    public function sabeelSuccessPayment()
    {
        // echo "YES"; exit;
        $service        =  new Services();
        $bookingModel   = new SabeelBookingModel();
        $CheckoutModel  = new CheckoutModel();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();

        $AccountModel            = new AccountModel();
        $Admin_transaction_Model = new Admin_transaction_Model();
		$User_transaction_Model  = new User_transaction_Model();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
        $ServiceCommisionModel   = new ServiceCommisionModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $session_id       = $this->request->getVar("session_id");
		$sabeel_id        = $this->request->getVar("sabeel_id");
		$service_type     = "sabeel";
		$active           = "active";

		$ota_id           = $this->request->getVar('ota_id');
		$total_cost       = $this->request->getVar('total_cost');
		$quantity         = $this->request->getVar('quantity');
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
            'sabeel_id' => [
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
            'quantity' => [
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

                $sabeelData = $sabeel->where('id', $sabeel_id)->first();

                if (empty($sabeelData)) 
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.sabeel_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $checkoutid  = $checkOutData['id'];
                $provider_id = $sabeelData['provider_id'];

                $rate = $sabeelData['price'];

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
                        'provider_id' => $sabeelData['provider_id'],
                        'sabeel_id' => $sabeel_id,
                        'user_id' => $logged_user_id,
                        'ota_id' => $ota_id,
                        'full_name' => $checkOutData['guest_fullname'],
                        'mobile' => $checkOutData['guest_contact_no'],
                        'total_price' => $total_cost,
                        'quantity' => $quantity,
                        'booking_status_user' => 'in-progress',
                        'booking_status_stripe' => 'open',
                        'booking_status' => 'pending',
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
                    ];

                    if ($bookingModel->insert($inprocessbooking)) {

                        $lastbooking_id = $bookingModel->insertID;
                        
                        if ($stripe_session_data['payment_status'] == 'paid') {    
                            
                            $confirm_booking = [
                                'booking_status_user' => 'confirm',
                                'booking_status_stripe' => $stripe_session_data->status,
                                'payment_status' => 'completed'
                            ];
                            $update_Booking = $bookingModel->update($lastbooking_id, $confirm_booking);
        
                            // SEND EAMIL TO PROVIDER on PAckage Booking
                            $Providerdata = $ProviderModel->where("id", $provider_id)->first();
                            $providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];
        
                            $data = array('user_role' => 'provider','user_name' => $checkOutData['guest_fullname'], 'provider_name' => $providerFullname, 'package_name'=>$sabeelData['name']);
                            $msg_template = view('emmail_templates/package_booking.php', $data);
                            $subject      = 'Package Booked';
                            $to_email     =  $Providerdata['email']; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);        
                            // SEND EAMIL TO USER on PAckage Booking
                            $data = array('user_role' => 'user','user_name' => $checkOutData['guest_fullname'], 'provider_name' => $providerFullname, 'package_name'=>$sabeelData['name']);
                            $msg_template = view('emmail_templates/package_booking.php', $data);
                            $subject      = 'Package Booked';
                            $to_email     =  $checkOutData['guest_email']; // user email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                            // EnD
        
                            // for  provider 
                            $providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $sabeelData['provider_id'])->first();
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
                                'service_id' => $sabeel_id,
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
                                'transaction_reason' => 'Sabeel Amount to Admin',
                                'currency_code' => 'SAR',
                                'transaction_amount' => $total_cost,
                                'transaction_id' => $transaction_id,
                                'transaction_status' => 'success',
                                'transaction_date' => date("Y-m-d"),
                                'service_type' => $service_type,
                                'service_id' => $sabeel_id,
                                'payment_method' => 'STRIPE'
                            ];
                            $User_transaction_Model->insert($user_transaction);

                            // PUSH NOTIFICATION
                            helper('notifications');
                            $db = db_connect();
                            $userinfo = $db->table('tbl_user')
                                ->select('*')
                                ->where('id', $_POST['logged_user_id'])
                                ->get()->getRow();

                            $title = "Sabeel Booking";
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
                        
                            $title = "Sabeel Booking";
                            $message = "Sabeel Booking recevied from a ".$checkOutData['guest_fullname']." for a ".$sabeelData['name'];
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

    // 08 OCT 2022 - RIZ SABEEL BOOKINGS
    public function bookingList()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $service->cors();

        $token            =  $this->request->getVar('authorization');
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

                elseif($user_role == 'provider'){ $whereCondition .= "e.provider_id = ".$logged_user_id." AND e.status = '1' "; }

                elseif($user_role == 'user'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = '1'"; }

                elseif($user_role == 'ota'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = '1'"; }

                $db = db_connect();
                $data = $db->table('tbl_sabeel_booking as e')
                    ->join('tbl_provider as p','p.id = e.provider_id')
                    ->join('tbl_sabeel as m','m.id = e.sabeel_id')
                    ->join('tbl_user as u','u.id = e.user_id')
                    ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, m.name, CONCAT(u.firstname,' ',u.lastname) as user_name")
                    // ->where('e.status','1')
                    ->where($whereCondition)
                    // ->orderBy('e.id', 'DESC')
                    ->orderBy("CASE WHEN booking_status = 'pending' THEN 1 ELSE 2 END")
                    ->orderBy('created_date', 'DESC')      
                    ->limit($limit, $offset)
                    ->get()->getResult();
                    
                $total =  $db->table('tbl_sabeel_booking as e')->where($whereCondition)->countAllResults();

                return $service->success(
                    [
                        'message'       =>  Lang('Language.list_success'),
                        'data'          =>  [
                            'total'            =>  $total,
                            'bookings'         =>  $data,
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

    public function bookingView()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $service->cors();

        $token            =  $this->request->getVar('authorization');
        $user_role        =  $this->request->getVar('logged_user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $enquiry_id       =  $this->request->getVar('booking_id');

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
            'logged_user_role' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'booking_id' => [
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
                    $info = $db->table('tbl_sabeel_booking as e')
                        ->join('tbl_provider as p','p.id = e.provider_id')
                        ->join('tbl_sabeel as m','m.id = e.sabeel_id')
                        ->join('tbl_user as u','u.id = e.user_id')
                        ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, m.name, CONCAT(u.firstname,' ',u.lastname) as user_name, p.supporter_no")
                        ->where('e.status','1')
                        ->where('e.id',$enquiry_id)
                        ->orderBy('e.id', 'DESC')
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
                        'message'   =>  Lang('Language.sabeel_not_found'),
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

    // COD PAYMENT - 14 OCT 2022 - RIZ
    public function sabeelCodBooking()
    {
        // echo "YES"; exit;
        $service        =  new Services();
        $bookingModel   = new SabeelBookingModel();
        $CheckoutModel  = new CheckoutModel();
        $sabeel         = new SabeelModel();
        $ProviderModel  = new ProviderModel();

        $AccountModel            = new AccountModel();
        $Admin_transaction_Model = new Admin_transaction_Model();
		$User_transaction_Model  = new User_transaction_Model();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
        $ServiceCommisionModel   = new ServiceCommisionModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $sabeel_id        = $this->request->getVar("sabeel_id");
		$service_type     = "sabeel";
		$active           = "active";

        $ota_id           = $this->request->getVar('ota_id');
		$total_cost       = $this->request->getVar('total_cost');
		$quantity         = $this->request->getVar('quantity');

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
            'sabeel_id' => [
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
            'quantity' => [
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
            'contact_no' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            // 'email' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
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

        if($checkToken ) {

            try{

                $sabeelData = $sabeel->where('id', $sabeel_id)->first();
                if (empty($sabeelData)) 
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.sabeel_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $provider_id = $sabeelData['provider_id'];
                $rate = $sabeelData['price'];

                // admin commission 
                $provider_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $provider_id)->where('user_role', 'provider')->first();
                if(!empty($provider_commision_data)){
                    $admin_commision_per = $provider_commision_data['commision_in_percent'];
                    $admin_percent = $admin_commision_per / 100;
                    $admin_amount = $admin_percent * $rate;
                } else { $admin_amount = 0; }

                // ota  commission
                $ota_commision_data = $ServiceCommisionModel->where('service_type', $service_type)->where('user_id', $ota_id)->where('user_role', 'ota')->first();
                if(!empty($ota_commision_data)){
                    $ota_commision = $ota_commision_data['commision_in_percent'];
                    $ota_precent = $ota_commision / 100;
                    $ota_ammount = $ota_precent * $rate;
                } else { $ota_ammount = 0; }

                $provider_amount = $rate - $admin_amount;

                $remaining_admin_comm_amount = $admin_amount - $ota_ammount;

                $inprocessbooking = [
                    'provider_id' => $provider_id,
                    'sabeel_id' => $sabeel_id,
                    'user_id' => $logged_user_id,
                    'ota_id' => $ota_id,
                    'full_name' => $full_name,
                    'mobile' => $contact_no,
                    'total_price' => $total_cost,
                    'quantity' => $quantity,
                    'booking_status' => 'pending',
                    'booking_status_user' => 'confirm',
                    'booking_status_stripe' => 'complete',
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
                    'created_date' => date('Y-m-d H:i:s'),
                ];

                if ($bookingModel->insert($inprocessbooking)) {

                    $lastbooking_id = $bookingModel->insertID;

                    // SEND EAMIL TO PROVIDER on PAckage Booking
                    $Providerdata = $ProviderModel->where("id", $provider_id)->first();
                    $providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];

                    $data = array('user_role' => 'provider','user_name' => $full_name, 'provider_name' => $providerFullname, 'package_name'=>$sabeelData['name']);
                    $msg_template = view('emmail_templates/package_booking.php', $data);
                    $subject      = 'Package Booked';
                    $to_email     =  $Providerdata['email']; // provider email
                    $filename = "";
                    $send     = sendEmail($to_email, $subject, $msg_template,$filename);
                    // SEND EAMIL TO USER on PAckage Booking
                    $data = array('user_role' => 'user','user_name' => $full_name, 'provider_name' => $providerFullname, 'package_name'=>$sabeelData['name']);
                    $msg_template = view('emmail_templates/package_booking.php', $data);
                    $subject      = 'Package Booked';
                    $to_email     =  $email; // user email
                    $filename = "";
                    $send     = sendEmail($to_email, $subject, $msg_template,$filename);                    // EnD

                    // for  provider 
                    $providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $sabeelData['provider_id'])->first();
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
                        'service_id' => $sabeel_id,
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
                        // 'payment_session_id' => $session_id
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
                        'transaction_reason' => 'Sabeel Amount to Admin',
                        'currency_code' => 'SAR',
                        'transaction_amount' => $total_cost,
                        'transaction_id' => $transaction_id,
                        'transaction_status' => 'success',
                        'transaction_date' => date("Y-m-d"),
                        'service_type' => $service_type,
                        'service_id' => $sabeel_id,
                        'payment_method' => 'STRIPE'
                    ];
                    $User_transaction_Model->insert($user_transaction);

                    // PUSH NOTIFICATION
                    helper('notifications');
                    $db = db_connect();
                    $userinfo = $db->table('tbl_user')
                        ->select('*')
                        ->where('id', $_POST['logged_user_id'])
                        ->get()->getRow();

                    $title = "Sabeel Booking";
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
                
                    $title = "Sabeel Booking";
                    $message = "Sabeel Booking recevied from a ".$full_name." for a ".$sabeelData['name'];
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
