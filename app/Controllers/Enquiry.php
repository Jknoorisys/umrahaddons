<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\EnquiryModel;
use App\Models\GuideEnquiryModel;
use App\Models\TransportModel;
use App\Models\PackageInquiryModel;

use App\Models\BookingModel;
use App\Models\MealsBookingModel;
use App\Models\SabeelBookingModel;


use Config\Services;
use Exception;

use App\Libraries\MailSender;
use App\Models\FullPackageEnquiry;
use App\Models\VisaEnquiry;

class Enquiry extends ResourceController
{
    public function index()
    {
      exit('No direct script access allowed.');
    }

    public function list()
    {
        $service   =  new Services();
        $enquiry   = new EnquiryModel();
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

                elseif($user_role == 'provider'){ $whereCondition .= "e.provider_id = ".$logged_user_id." AND e.status = '1' "; }

                elseif($user_role == 'user'){ $whereCondition .= "e.user_id = ".$logged_user_id." AND e.status = '1'"; }

                $db = db_connect();
                $data = $db->table('meals_booking as e')
                    ->join('tbl_provider as p','p.id = e.provider_id')
                    ->join('tbl_meals as m','m.id = e.meals_id')
                    ->join('tbl_user as u','u.id = e.user_id')
                    ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, m.title, CONCAT(u.firstname,' ',u.lastname) as user_name")
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
        $service   =  new Services();
        $enquiry   = new EnquiryModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

        $provider_id       =  $this->request->getVar('provider_id');
        $meals_id          =  $this->request->getVar('meals_id');
        $ota_id            =  $this->request->getVar('ota_id');
        $start_date        =  $this->request->getVar('start_date');
        $end_date          =  $this->request->getVar('end_date');
        $meals_type        =  $this->request->getVar('meals_type');
        $meals_service     =  $this->request->getVar('meals_service');
        $no_of_person      =  $this->request->getVar('no_of_person');
        $notes             =  $this->request->getVar('notes');

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
            'provider_id' => [
                'rules'         =>  'required|numeric',
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
            'ota_id' => [
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
            'meals_service' => [
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
            try 
            {
                $data = array(
                    'provider_id'   => $provider_id,
                    'meals_id'      => $meals_id,
                    'user_id'       => $logged_user_id,
                    'ota_id'        => $ota_id,
                    'start_date'    => $start_date, 
                    'end_date'      => $end_date,
                    'meals_type'    => $meals_type,
                    'meals_service' => $meals_service,
                    'no_of_person'  => $no_of_person,
                    'notes'         => (isset($notes))?$notes:'',
                    'name'          => (isset($name))?$name:'',
                    'mobile'        => (isset($mobile))?$mobile:'',
                    'created_date'  => date('Y-m-d H:i:s')
                );

                if($enquiry->insert($data)) 
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
        $enquiry   = new EnquiryModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $enquiry_id         =  $this->request->getVar('enquiry_id');

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
                    $info = $db->table('meals_booking as e')
                        ->join('tbl_provider as p','p.id = e.provider_id')
                        ->join('tbl_meals as m','m.id = e.meals_id')
                        ->join('tbl_user as u','u.id = e.user_id')
                        ->select("e.*, CONCAT(p.firstname,' ',p.lastname) as provider_name, m.title, CONCAT(u.firstname,' ',u.lastname) as user_name, p.supporter_no")
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

    // Accept / Reject enquiries by AMIND / PROVIDER
    public function changeEnquiryStatus()
    {
        $service         =  new Services();
        $enquiry         = new EnquiryModel();
        $guide_enquiry   = new GuideEnquiryModel();
        $transport_enquiry   = new TransportModel();
        $package_enqiry   = new PackageInquiryModel();
        $full_package_enqiry   = new FullPackageEnquiry();
        $visa_enquiry     = new VisaEnquiry();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        
        $enquiry_id       =  $this->request->getVar('enquiry_id');
        $service_type     =  $this->request->getVar('service_type');
        $status           =  $this->request->getVar('status');
        $reason           =  $this->request->getVar('reason');

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
            'service_type' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'status' => [
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

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken)
        {
            try{
                
                if($service_type=='package') {
                    $isExist = $package_enqiry->where(['id'=> $enquiry_id])->first();
                } else if($service_type=='guide') {
                    $isExist = $guide_enquiry->where(['id'=> $enquiry_id])->first();
                } else if($service_type=='transport') {
                    $isExist = $transport_enquiry->where(['id'=> $enquiry_id])->first();
                } else if($service_type=='visa') {
                    $isExist = $visa_enquiry->where(['id'=> $enquiry_id])->first();
                } else if($service_type=='full-package') {
                    $isExist = $full_package_enqiry->where(['id'=> $enquiry_id])->first();
                }

                
                if(empty($isExist)){
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.enquiry_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                } else {
                    if($isExist['booking_status']=='accept'){
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.already_accepted'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
                    if($isExist['booking_status']=='reject'){
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.already_rejected'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
                    
                    $updateData = array(
                        'booking_status'  =>  $status.'ed',
                        'reject_reason'   =>  isset($reason) ? $reason : "",
                        'booking_action'  =>  $user_role,
                    );

                    if($service_type=='package') {
                        $update = $package_enqiry->update($enquiry_id, $updateData);
                    } else if($service_type=='guide') {
                        $update = $guide_enquiry->update($enquiry_id, $updateData);
                    } else if($service_type=='transport') {
                        $update = $transport_enquiry->update($enquiry_id, $updateData);
                    } else if($service_type=='visa') {
                        $update = $visa_enquiry->update($enquiry_id, $updateData);
                    } else if($service_type=='full-package') {
                        $update = $full_package_enqiry->update($enquiry_id, $updateData);
                    } 

                    if($update){
                        $user_id = $isExist['user_id'];
                        $db = db_connect();
                        $info = $db->table('tbl_user')
                            ->select("*")
                            ->where('id',$user_id)
                            ->get()->getRow();
                        
                        // SEND EMIAL ON ACCEPT/REJECT Request
                        if($service_type=='package'){
                            $data = array('status' => $status, 'user_name' => $isExist['full_name']);
                            $msg_template = view('emmail_templates/meals_enquiry.php', $data);
                            $subject      = 'Package Enquiry';
                            $to_email     =  $info->email; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                        }

                        if($service_type=='guide'){
                            $data = array('status' => $status, 'user_name' => $isExist['name']);
                            $msg_template = view('emmail_templates/guide_enquiry.php', $data);
                            $subject      = 'Guide Enquiry';
                            $to_email     =  $info->email; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                        }
                        // EnD

                         // PUSH NOTIFICATION
                         helper('notifications');
                         $db = db_connect();
                         $userinfo = $db->table('tbl_user')
                             ->select('*')
                             ->where('id', $user_id)
                             ->get()->getRow();
                         
                            if($service_type=='guide') {
                               $title = "Guide Enquiry";
                            } elseif ($service_type=='package') {
                                $title = "Package Enquiry";
                            } elseif ($service_type=='transport') {
                                $title = "Transport Enquiry";
                            } elseif ($service_type=='visa') {
                                $title = "Visa Enquiry";
                            } elseif ($service_type=='full-package') {
                                $title = "Full Package Enquiry";
                            }

                         if($status=='accept'){
                             $message = "Your enquiry has been accepted. Kindly check your e-mail, the provider will contact you soon.";
                         } else {
                             $message = "Sorry, your enquiry has been rejected, you can contact another provider.";
                         }
                         if($userinfo){
                            $fmc_ids = $userinfo->device_token ? array($userinfo->device_token) : '';
                        
                            $notification = array(
                                'title' => $title ,
                                'message' => $message,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                                'date' => date('Y-m-d H:i'),
                            );
                            if($userinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }
                        }
                         // EnD

                        return $service->success([
                                    'message'       =>  Lang('Language.update_success'),
                                    'data'          =>  '',
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
                        'message'   =>  Lang('Language.update_failed'),
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

    // Accept / Reject Bookings Package/Meals/Sabeel
    public function changeBookingStatus()
    {
        // echo "YdddES"; exit;
        $service          =  new Services();
        $package          =  new BookingModel();
        $meals            =  new MealsBookingModel();
        $sabeel           =  new SabeelBookingModel();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        
        $service_type     =  $this->request->getVar('service');
        $booking_id       =  $this->request->getVar('booking_id');
        $status           =  $this->request->getVar('status');
        $reason           =  $this->request->getVar('reason');

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
            'service' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            'status' => [
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
        
        $checkToken = $service->getAccessForSignedUser($token, $user_role);
        
        if($checkToken){
            try{

                if($service_type=='package') {
                    $isExist = $package->where(['id'=> $booking_id])->first();
                } else if($service_type=='sabeel') {
                    $isExist = $sabeel->where(['id'=> $booking_id])->first();
                } else if($service_type=='meals') {
                    $isExist = $meals->where(['id'=> $booking_id])->first();
                }

                if(empty($isExist))
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Booking Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                } else 
                {
                    if($isExist['booking_status']=='accepted'){
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.already_accepted'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }
                    if($isExist['booking_status']=='rejected'){
                        return $service->fail(
                            [
                                'errors'    =>  "",
                                'message'   =>  Lang('Language.already_rejected'),
                            ],
                            ResponseInterface::HTTP_BAD_REQUEST,
                            $this->response
                        );
                    }

                    $updateData = array(
                        'booking_status'  =>  $status.'ed',
                        'reject_reason'   =>  isset($reason) ? $reason : "",
                    );

                    if($service_type=='package') {
                        $update = $package->update($booking_id, $updateData);
                    } else if($service_type=='sabeel') {
                        $update = $sabeel->update($booking_id, $updateData);
                    } else if($service_type=='meals') {
                        $update = $meals->update($booking_id, $updateData);
                    } 

                    if($update)
                    {
                        $user_id = $isExist['user_id'];
                        $db = db_connect();
                        $info = $db->table('tbl_user')
                            ->select("*")
                            ->where('id',$user_id)
                            ->get()->getRow();
                        
                            helper('auth_helper');

                        // SEND EMIAL ON ACCEPT/REJECT Request
                        if($service_type=='package'){
                            $data = array('status' => $status, 'user_name' => $isExist['guest_fullname']);
                            $msg_template = view('emmail_templates/meals_enquiry.php', $data);
                            $subject      = 'Package Booking';
                            $to_email     =  $info->email; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                        }

                        if($service_type=='sabeel'){
                            $data = array('status' => $status, 'user_name' => $isExist['full_name']);
                            $msg_template = view('emmail_templates/meals_enquiry.php', $data);
                            $subject      = 'Sabeel Booking';
                            $to_email     =  $info->email; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                        }

                        if($service_type=='meals'){
                            $data = array('status' => $status, 'user_name' => $isExist['full_name']);
                            $msg_template = view('emmail_templates/meals_enquiry.php', $data);
                            $subject      = 'Meals Booking';
                            $to_email     =  $info->email; // provider email
                            $filename = "";
                            $send     = sendEmail($to_email, $subject, $msg_template,$filename);                        }
                        // EnD

                        // PUSH NOTIFICATION
                        helper('notifications');
                        $db = db_connect();
                        $userinfo = $db->table('tbl_user')
                            ->select('*')
                            ->where('id', $user_id)
                            ->get()->getRow();
                         
                        if($service_type=='package') {
                            $title = "Package Booking";
                        } elseif ($service_type=='sabeel') {
                            $title = "Sabeel Booking";
                        } elseif ($service_type=='meals') {
                            $title = "Meals Booking";
                        }

                        if($status=='accept'){
                            $message = "Your booking has been accepted. Kindly check your e-mail, the provider will contact you soon.";
                        } else {
                            $message = "Sorry, your booking has been rejected, you can contact another provider.";
                        }
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
                                'message'       =>  Lang('Language.update_success'),
                                'data'          =>  '',
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
                        'message'   =>  Lang('Language.update_failed'),
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
