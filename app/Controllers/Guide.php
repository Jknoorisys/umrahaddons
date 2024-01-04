<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\GuideModel;
use App\Models\GuideDocModel;
use App\Models\GuideEnquiryModel;

use Config\Services;
use Exception;

class Guide extends ResourceController
{

    public function index()
    {
      exit('No direct script access allowed.');
    }

    public function updateProfile()
    {
      $service   =  new Services();
      $guide     = new GuideModel();
      $GuideDocModel = new GuideDocModel();
      $service->cors();

      $token             =  $this->request->getVar('token');
      $logged_user_id    =  $this->request->getVar('logged_user_id');
      $user_role         =  $this->request->getVar('user_role');
      
      $firstname         =  $this->request->getVar('firstname');
      $lastname          =  $this->request->getVar('lastname');
      $mobile            =  $this->request->getVar('mobile');
      $dob               =  $this->request->getVar('dob');
      $nationality       =  $this->request->getVar('nationality');
      $education         =  $this->request->getVar('education');
      $experience        =  $this->request->getVar('experience');
      $home_address      =  $this->request->getVar('address');
      $city              =  $this->request->getVar('city');
      $country           =  $this->request->getVar('country');
      $about_us          =  $this->request->getVar('about_us');
      $spoken_language   =  $this->request->getVar('spoken_language');

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
        'firstname' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'lastname' => [
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
        'dob' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'nationality' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'education' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'experience' => [
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
        'city' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'country' => [
            'rules'         =>  'required',
            'errors'        => [
                'required'      =>  Lang('Language.required'),
            ]
        ],
        'about_us' => [
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
              $isExist = $guide->where(['id'=> $logged_user_id])->first();
              if(empty($isExist))
              {
                  return $service->fail(
                      [
                          'errors'    =>  "",
                          'message'   =>  Lang('Language.guide_not_found'),
                      ],
                      ResponseInterface::HTTP_BAD_REQUEST,
                      $this->response
                  );
              } else 
              {
                  $old_cover_image = $isExist['cover_pic'];
                  $cover_image  =  $this->request->getFile('cover_image');

                  if(!empty($_FILES["cover_image"]["tmp_name"]))
                  {
                      $validated = $this->validate([
                          'file' => [
                              'uploaded[cover_image]',
                              'mime_in[cover_image,image/jpg,image/jpeg,image/gif,image/png]',
                              'max_size[cover_image,5120]',
                          ],
                      ]);

                      if($validated && !$cover_image->hasMoved())
                      {
                          $file_path = 'public/assets/uploads/guide/';
                          if (!file_exists($file_path)) {
                              mkdir($file_path, 0777, true);
                          }

                          $newName = $cover_image->getRandomName();
                          $cover_image->move($file_path, $newName);
                          $picture = $file_path . $newName;

                          if (!empty($isExist['cover_pic']) && file_exists($isExist['cover_pic'])) {
                              unlink($isExist['cover_pic']);
                          }
                      }
                      $cover_image = $picture;
                  } else { $cover_image = $old_cover_image; }

                  $updateData = array(
                      'firstname'    => $firstname,
                      'lastname'     => $lastname,
                      'contact'      => $mobile,
                      'cover_pic'    => $cover_image,
                      'dob'          => $dob, 
                      'nationality'  => $nationality,
                      'education'    => $education,
                      'experience'   => $experience,
                      'home_address' => (isset($home_address))?$home_address:'',
                      'city'         => $city,
                      'country'      => $country,
                      'about_us'     => $about_us,
                      'language'     => $spoken_language,
                      'updated_date' => date('Y-m-d H:i:s')
                  );

                  if($guide->update($logged_user_id, $updateData))
                  {
                      $data = $guide->where( ['id'=> $logged_user_id] )->first();
                      $data['guide_doc'] = $GuideDocModel->select('id,guide_doc')->where("guide_id", $logged_user_id)->findAll();

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

    public function updateDocumnet()
    {
      $service   =  new Services();
      $guideDoc     = new GuideDocModel();
      $service->cors();

      $token             =  $this->request->getVar('token');
      $logged_user_id    =  $this->request->getVar('logged_user_id');
      $user_role         =  $this->request->getVar('user_role');
      $document_id       =  $this->request->getVar('document_id');

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
          'document_id' => [
              'rules'         =>  'required|numeric',
              'errors'        => [
                  'required'      =>  Lang('Language.required'),
              ]
          ]
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
          try {
                $document  =  $this->request->getFile('document');
                $validated = $this->validate([
                  'file' => [
                      'uploaded[document]',
                      'mime_in[document,image/jpg,image/jpeg,image/png]',
                      'max_size[document,5120]',
                  ],
                ]);

              if($validated)
                {   
                    $info = $guideDoc->find($document_id);
                    if (!empty($info['guide_doc']) && file_exists($info['guide_doc'])) {
                        unlink($info['guide_doc']);
                    }

                    $file_path = 'public/assets/uploads/guide/documents/';
                    $tempName = $document->getRandomName();
                    $document->move($file_path, $tempName);
                    $doc = $file_path . $tempName;

                    $data = array(
                      'guide_id'      => $logged_user_id,
                      'guide_doc'     => $doc,
                      'updated_date'  => date('Y-m-d H:i:s')
                    );

                    if($guideDoc->update($document_id, $data)) 
                    {
                      return $service->success([
                              'message'       =>  Lang('Language.update_success'),
                              'data'          =>  $doc
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

    public function updateAvatar()
    {
      $service   =  new Services();
      $guide     = new GuideModel();
      $service->cors();

      $token             =  $this->request->getVar('token');
      $logged_user_id    =  $this->request->getVar('logged_user_id');
      $user_role         =  $this->request->getVar('user_role');

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
          ]
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
          try {
                $avatar  =  $this->request->getFile('avatar');
                $validated = $this->validate([
                  'file' => [
                      'uploaded[avatar]',
                      'mime_in[avatar,image/jpg,image/jpeg,image/png]',
                      'max_size[avatar,5120]',
                  ],
                ]);

              if($validated)
                {   
                    $file_path = 'public/assets/uploads/guide/';
                    $tempName = $avatar->getRandomName();
                    $avatar->move($file_path, $tempName);
                    $avatar = $file_path . $tempName;

                    $data = array(
                      'profile_pic'     => $avatar,
                      'updated_date'  => date('Y-m-d H:i:s')
                    );

                    if($guide->update($logged_user_id, $data)) 
                    {
                      return $service->success([
                              'message' =>  Lang('Language.update_success'),
                              'data'    =>  ['avatar_url' => $avatar],
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
                    'message'   =>  Lang('Language.upload_failed'),
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

    public function allGuide()
    {
        $service   =  new Services();
        $guide     = new GuideModel();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('user_role');
        $logged_user_id   =  $this->request->getVar('logged_user_id');

        $language       =  $this->request->getVar('spoken_language');
        $experience      =  $this->request->getVar('experience');
        $status      =  $this->request->getVar('status');

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

        try {

            $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
            $offset        = ( $currentPage - 1 ) * PER_PAGE;
            $limit         =  PER_PAGE;

            $whereCondition = "m.status = 'active' AND is_deleted = 0 ";

            if(isset($language) && $language!='')
            {
                $whereCondition .= "AND FIND_IN_SET ('".$language."', m.language)";
            }

            if(isset($experience) && $experience>0){
                $whereCondition .= " AND m.experience = '" . $experience . "' ";
            }

            if(isset($status) && $status!=''){
                // $whereCondition .= " AND m.firstname LIKE'%" . $guide_name . "%' OR m.lastname LIKE'%" . $guide_name . "%'";
                $whereCondition .= " AND m.is_verify = '" . $status . "' ";
            }

            $db = db_connect();
            $guides = $db->table('tbl_guide as m')
                ->select('m.*')
                ->where($whereCondition)
                ->orderBy('m.is_verify', 'ASC')
                ->orderBy('m.id', 'DESC')
                ->limit($limit, $offset)
                ->get()->getResult();

            // echo $db->getLastQuery()->getQuery(); exit; 

            $total =  $db->table('tbl_guide as m')->where($whereCondition)->countAllResults();    
            return $service->success(
              [
                  'message'       =>  Lang('Language.list_success'),
                  'data'          =>  [
                      'total'             =>  $total,
                      'guideList'         =>  $guides,
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

    public function sendEnquiry()
    {
        // echo "YES"; exit;
        $service   =  new Services();
        $enquiry   = new GuideEnquiryModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

        $guide_id          =  $this->request->getVar('guide_id');
        $start_date        =  $this->request->getVar('start_date');
        // $end_date          =  $this->request->getVar('end_date');
        // $no_of_person      =  $this->request->getVar('no_of_person');
        $notes             =  $this->request->getVar('notes');

        $name              =  $this->request->getVar('name');
        $mobile            =  $this->request->getVar('mobile');

        $package_duration  =  $this->request->getVar('package_duration');

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
            'guide_id' => [
                'rules'         =>  'required|numeric',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            // 'no_of_person' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
            'start_date' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                ]
            ],
            // 'end_date' => [
            //     'rules'         =>  'required',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //     ]
            // ],
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
                    'guide_id'      => $guide_id,
                    'user_id'       => $logged_user_id,
                    'start_date'    => $start_date, 
                    // 'end_date'      => $end_date,
                    // 'no_of_person'  => $no_of_person,
                    'package_duration' => (isset($package_duration)) ? $package_duration :'',
                    'notes'         => (isset($notes))?$notes:'',
                    'name'          => (isset($name))?$name:'',
                    'mobile'        => (isset($mobile))?$mobile:'',
                    'created_date'  => date('Y-m-d H:i:s')
                );

                if($enquiry->insert($data)) 
                {
                    // PUSH NOTIFICATION
                    helper('notifications');
                    $db = db_connect();
                    $userinfo = $db->table('tbl_user')
                        ->select('*')
                        ->where('id', $_POST['logged_user_id'])
                        ->get()->getRow();

                    if($user_role!='user'){
                        $guideinfo = $db->table('tbl_guide')
                        ->select('*')
                        ->where('id', $guide_id)
                        ->get()->getRow();    

                        $title = "Guide Inquiry";
                        $message = "Inquiry received from a ".$name." for a guide";    
                        $fmc_ids = array($guideinfo->device_token);
                        $notification = array(
                            'title' => $title ,
                            'message' => $message,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
                            'date' => date('Y-m-d H:i'),
                        );
                        if($guideinfo->device_type!='web'){ sendFCMMessage($notification, $fmc_ids); }
                    }

                    $title = "Guide Inquiry";
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

    public function EnquiryList()
    {
        $service         =  new Services();
        $guide_enquiry   = new GuideEnquiryModel();
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
            try{

                $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
                $offset        = ( $currentPage - 1 ) * PER_PAGE;
                $limit         =  PER_PAGE;

                $whereCondition = "e.status = '1' ";

                if($user_role == 'guide'){ 
                    $whereCondition .= "AND e.guide_id = '".$logged_user_id."'"; 
                }
                
                if($user_role == 'user'){ 
                    $whereCondition .= "AND e.user_id = '".$logged_user_id."'"; 
                }

                $db = db_connect();
                $data = $db->table('tbl_guide_enquiry as e')
                    ->join('tbl_guide as g','g.id = e.guide_id')
                    ->join('tbl_user as u','u.id = e.user_id')
                    ->select("e.*, CONCAT(u.firstname,' ',u.lastname) as user_name, CONCAT(g.firstname,' ',g.lastname) as guide_name")
                    ->where($whereCondition)
                    ->orderBy('e.id', 'DESC')
                    ->limit($limit, $offset)
                    ->get()->getResult();
                    
                $total =  $db->table('tbl_guide_enquiry as e')->where($whereCondition)->countAllResults();

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

    public function EnquiryView()
    {
        $service         =  new Services();
        $guide_enquiry   = new GuideEnquiryModel();
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
            try {
                    $db = db_connect();
                    $info = $db->table('tbl_guide_enquiry as e')
                        ->join('tbl_guide as g','g.id = e.guide_id')
                        ->join('tbl_user as u','u.id = e.user_id')
                        ->select("e.*, CONCAT(u.firstname,' ',u.lastname) as user_name, CONCAT(g.firstname,' ',g.lastname) as guide_name")
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
                            'message'   =>  Lang('Language.enquiry_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                 }

            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.enquiry_not_found'),
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

    public function Info()
    {
        $service   =  new Services();
        $guide     = new GuideModel();
        $GuideDocModel = new GuideDocModel();
        $service->cors();

        $guide_id    =  $this->request->getVar('guide_id');

        try {
                $info = $guide->where(['id'=> $guide_id])->first();
                $info['guide_doc'] = $GuideDocModel->select('id,guide_doc')->where("guide_id", $guide_id)->findAll();

                if(empty($info))
                {
                  return $service->fail(
                      [
                          'errors'    =>  "",
                          'message'   =>  Lang('Language.guide_not_found'),
                      ],
                      ResponseInterface::HTTP_BAD_REQUEST,
                      $this->response
                  );
                } else 
                {
                  return $service->success([
                        'message'       =>  Lang('Language.Guide Details'),
                        'data'          =>  $info,
                    ],
                        ResponseInterface::HTTP_CREATED,
                        $this->response
                  );
                }                
            } catch (Exception $e) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.guide_not_found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }
    }

    public function deleteAccount()
    {
        $service   =  new Services();
        $guide     = new GuideModel();
        $service->cors();

        $token             =  $this->request->getVar('token');
        $logged_user_id    =  $this->request->getVar('logged_user_id');
        $user_role         =  $this->request->getVar('user_role');

        $checkToken = $service->getAccessForSignedUser($token, $user_role);

        if($checkToken )
        {
            try 
            {
                $info = $guide->where(['id'=> $logged_user_id])->first();

                if(empty($info))
                {
                    return $service->fail(
                        [
                            'errors'    =>  "",
                            'message'   =>  Lang('Language.guide_not_found'),
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                } else 
                {
                    $updateData = array(
                        'contact'      => '',
                        'email'      => '',
                        'password'      => '',
                        'status'      => '',
                        'reason'      => '',
                        'token'      => '',
                        'language'     => '',
                        'profile_pic'     => '',
                        'cover_pic'    => '',
                        'dob'          => '', 
                        'nationality'  => '',
                        'education'    => '',
                        'experience'   => '',
                        'home_address' => '',
                        'city'         => '',
                        'country'      => '',
                        'about_us'     => '',
                        'updated_date' => date('Y-m-d H:i:s'),
                        'is_deleted' => 1,
                    );
                
                    if($guide->update($logged_user_id, $updateData)){
                        return $service->success([
                            'message'       =>  Lang('Language.Account deleted successfully'),
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
                        'message'   =>  Lang('Language.guide_not_found'),
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
   
} // class end

/* End of file Guide.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Guide.php */