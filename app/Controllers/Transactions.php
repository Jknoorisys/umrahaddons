<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\Admin_transaction_Model;

use Config\Services;
use Exception;

class Transactions extends ResourceController
{
    public function index()
    {
        exit('No direct script access allowed.');
    }

    public function transactionList()
    {
        $service   =  new Services();
        $model     = new Admin_transaction_Model();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $provider_id        = $this->request->getVar('provider_id');
        $ota_id             = $this->request->getVar('ota_id');
        $package_id         = $this->request->getVar('package_id');
        $transaction_type   = $this->request->getVar('transaction_type');
        $transaction_status = $this->request->getVar('transaction_status');

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
            'token' => [
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

        if($checkToken){
            try {
                    $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
                    $offset        = ( $currentPage - 1 ) * PER_PAGE;
                    $limit         =  PER_PAGE;

                    $whereCondition = '';

                    if($user_role == 'admin'){ $whereCondition .= "t.admin_id = " . $logged_user_id; }

                    if(isset($provider_id) && $provider_id>0){
                        $whereCondition .= " AND t.user_id = " . $provider_id . " AND t.user_type = 'provider' " ;
                    }

                    if(isset($ota_id) && $ota_id>0){
                        $whereCondition .= " AND t.user_id = " . $ota_id . " AND t.user_type = 'ota' " ;
                    }

                    if(isset($package_id) && $package_id>0){
                        $whereCondition .= " AND t.service_id = " . $package_id . " AND t.service_type = 'package' " ;
                    }

                    if(isset($transaction_type) && $transaction_type!=''){
                        $whereCondition .= " AND t.transaction_type = '".$transaction_type."' " ;
                    }

                    if(isset($transaction_status) && $transaction_status!=''){
                        $whereCondition .= " AND t.transaction_status = '".$transaction_status."' " ;
                    }


                    $db = db_connect();
                    $transaction = $db->table('tbl_admin_transactions as t')
                        // ->join('tbl_provider as p','p.id = m.provider_id')
                        // ->join('tbl_cuision_master as c','c.id = m.cuisine_id')
                        ->select('t.*')
                        ->where($whereCondition)
                        ->orderBy('t.id', 'DESC')
                        ->limit($limit, $offset)
                        ->get()->getResult();

                    // echo $db->getLastQuery()->getQuery(); exit;

                    $total =  $db->table('tbl_admin_transactions as t')->where($whereCondition)->countAllResults();

                    return $service->success(
                        [
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  [
                                'total'             =>  $total,
                                'list'         =>  $transaction,
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

    public function transactionDetails()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $model     = new Admin_transaction_Model();
        $service->cors();

        $token            =  $this->request->getVar('token');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');
        $transaction_id   =  $this->request->getVar('transaction_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'token' => [
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
            'transaction_id' => [
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
            try {
                    $db = db_connect();

                    $transaction_details = $db->table('tbl_admin_transactions as t')
                        // ->join('tbl_provider as p','p.id = t.provider_id')
                        ->select('t.*')
                        ->where("t.id = " . $transaction_id)
                        ->get()->getResult();

                    return $service->success(
                        [
                            'message'       =>  Lang('Language.list_success'),
                            'data'          =>  [
                                'transaction_details'  =>  $transaction_details,
                            ]
                        ],
                        ResponseInterface::HTTP_OK,
                        $this->response
                    );    

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.details_fetch_failed'),
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
