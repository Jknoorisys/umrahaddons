<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\BookingModel;

use Config\Services;
use Exception;

class Bookings extends ResourceController
{
    public function index()
    {
      exit('No direct script access allowed.');
    }

    public function packageBookings()
    {
      $service   =  new Services();
      $service->cors();

      $token            =  $this->request->getVar('token');
      $pageNo           =  $this->request->getVar('pageNo');
      $user_role        =  $this->request->getVar('user_role');
      $logged_user_id   =  $this->request->getVar('logged_user_id');

      $package_id       =  $this->request->getVar('package_id');
      $booking_status   =  $this->request->getVar('booking_status');
      $provider_id      =  $this->request->getVar('provider_id'); 
      $ota_id           =  $this->request->getVar('ota_id'); 
      $payment_status   =  $this->request->getVar('payment_status'); 
      $booking_date     =  $this->request->getVar('booking_date'); 

      $rules = 
      [
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

          $whereCondition = " booking_status_user != 'in-progress' ";

          if( isset($package_id) && $package_id>0 ){
              $whereCondition .= " AND b.service_id = " . $package_id ;
          }

          if( isset($booking_status) && $booking_status!='' ){
              $whereCondition .= " AND b.booking_status	= '".$booking_status."' "  ;
          }

          if( isset($provider_id) && $provider_id>0 ){
              $whereCondition .= " AND b.provider_id	= " . $provider_id ;
          }

          if( isset($ota_id) && $ota_id>0 ){
              $whereCondition .= " AND b.ota_id	= " . $ota_id ;
          }

          if( isset($payment_status) && $payment_status!='' ){
              $whereCondition .= " AND b.payment_status	= '".$payment_status."' "  ;
          }

          if( isset($booking_date) && $booking_date!='' ){
              $whereCondition .= " AND b.booked_date = '".$booking_date."' "  ;
          }

          if($user_role=='ota'){
            $whereCondition .= " AND b.ota_id = " . $logged_user_id ;
          }

          $db = db_connect();
          $bookings = $db->table('tbl_booking as b')
              ->select('b.*, p.package_title as package_name, CONCAT(u.firstname," ",u.lastname) as user_name, CONCAT(pro.firstname," ",pro.lastname) as provider_name, CONCAT(o.firstname," ",o.lastname) as ota_name')
              ->join('tbl_package as p','p.id = b.service_id')
              ->join('tbl_user as u','u.id = b.user_id')
              ->join('tbl_provider as pro','pro.id = b.provider_id')
              ->join('tbl_ota as o','o.id = b.ota_id')
              ->where($whereCondition)
            //   ->orderBy('b.id', 'DESC')
              ->orderBy("CASE WHEN booking_status = 'pending' THEN 1 ELSE 2 END")
              ->orderBy('created_date', 'DESC')      
              ->limit($limit, $offset)
              ->get()->getResult();

        //   echo $db->getLastQuery()->getQuery();exit;

          $total =  $db->table('tbl_booking as b')->where($whereCondition)->countAllResults();

          return $service->success(
            [
                'message'       =>  Lang('Language.list_success'),
                'data'          =>  [
                    'total'             =>  $total,
                    'bookingList'       =>  $bookings,
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
      else 
      {
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

    public function bookingDetails()
    {
      $service   =  new Services();
      $service->cors();

      $token            =  $this->request->getVar('token');
      $user_role        =  $this->request->getVar('user_role');
      $logged_user_id   =  $this->request->getVar('logged_user_id');
      $booking_id       =  $this->request->getVar('booking_id');

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

      if($checkToken)
        {
            try 
            {
              $db = db_connect();
              $bookingData = $db->table('tbl_booking as b')->where('b.id', $booking_id)->get()->getRowArray();
              if (empty($bookingData)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Booking Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
              }

              $packageData = $db->table('tbl_package as p')->where('p.id', $bookingData['service_id'])->get()->getRowArray();
                if (empty($packageData)) {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Package Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }

                $query = $db->table('tbl_booking as b')
                            ->select('b.*, p.package_title as package_name, p.main_img as img_url, CONCAT(u.firstname," ",u.lastname) as user_name')
                            ->join('tbl_package as p', 'p.id = b.service_id')
                            ->join('tbl_user as u', 'u.id = b.user_id')
                            ->where('b.id', $booking_id);

                            if ($packageData['package_type'] == 'group') {
                                $query->join('tbl_vehicle_master as v', 'v.id = b.cars');
                                $query->select('v.name as vehicle_type');
                            }
                            
                            $isExist = $query->get()->getRowArray();
              
            //   $isExist = $db->table('tbl_booking as b')
            //     ->select('b.*, p.package_title as package_name, p.main_img as img_url, CONCAT(u.firstname," ",u.lastname) as user_name, v.name as vehicle_type')
            //     ->join('tbl_package as p','p.id = b.service_id')
            //     ->join('tbl_user as u','u.id = b.user_id')
            //     ->join('tbl_vehicle_master as v','v.id = b.cars')
            //     ->where('b.id', $booking_id)
            //     ->get()->getRowArray();
              
                if($user_role=='admin' OR $user_role=='ota'){
                  $isExist['provider_details'] = $db->table('tbl_provider')->select('*')->where('id', $isExist['provider_id'])->get()->getRowArray();
                  $isExist['ota_details'] = $db->table('tbl_ota')->select('*')->where('id', $isExist['ota_id'])->get()->getRowArray();
                } 

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
                        'message'   =>  Lang('Language.Booking Not Found'),
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

    public function markCompleted()
    {
       $service   =  new Services();
       $service->cors();
       $booking   = new BookingModel();

       $token            =  $this->request->getVar('token');
       $user_role        =  $this->request->getVar('user_role');
       $logged_user_id   =  $this->request->getVar('logged_user_id');
       $booking_id       =  $this->request->getVar('booking_id');

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

        if($checkToken)
        {
            try {
                $isExist = $booking->where(['id'=> $booking_id])->first();
            
                if($isExist){

                    $db = db_connect();
                    $result = $db->table('tbl_package_movment')
                    ->select('MAX(day) as total')
                    ->where('package_id', $isExist['service_id'])
                    ->get()->getRowArray();
                    
                    $current_date = date('d-m-Y'); 
                    
                    $currentDate=date_create($current_date);
                    $bookingDate=date_create($isExist['from_date']);
                    
                    $diff=date_diff($bookingDate, $currentDate);
                    
                    $updateData = array(
                        'action_by'  =>  $user_role,
                        'action_by_id'  =>  $logged_user_id,
                        'action'  => 'completed'
                    );
                    
                    if($diff->format("%R%a")>=$result['total'])
                    {
                        $update = $booking->update($booking_id, $updateData);
                        return $service->success([
                                    'message'       =>  Lang('Language.update_success'),
                                    'data'          =>  '',
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

                  //  echo $diff->format("%R%a");  exit; 

                } else {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.Booking Not Found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                 }
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Booking Not Found'),
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
