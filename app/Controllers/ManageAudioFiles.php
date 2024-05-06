<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AudioFiles;
use Exception;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ManageAudioFiles extends BaseController
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

    public function list()
    {
        $service           =  new Services();
        $service->cors();

        $pageNo           =  $this->request->getVar('pageNo');
        $user_role        =  $this->request->getVar('logged_user_role');

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

        try{

            $currentPage   = ( !empty( $pageNo ) ) ? $pageNo : 1;
            $offset        = ( $currentPage - 1 ) * PER_PAGE;
            $limit         =  PER_PAGE;

            $whereCondition = "";

            if(isset($search) && $search!=''){
                $whereCondition .= "s.title LIKE'%" . $search . "%' AND ";
            }

            if($user_role == 'admin'){ $whereCondition .= "s.status != '2'"; } 

            if($user_role == 'user'){ $whereCondition .= "s.status = '1'"; } 

            if($user_role == 'provider'){ $whereCondition .= "s.status = '1'"; }

            // By Query Builder
            $db = db_connect();
            $appData = $db->table('tbl_audio_files as s')
                                ->where($whereCondition)
                                ->orderBy('s.id', 'DESC')
                                ->limit($limit, $offset)
                                ->get()->getResult();
            

            $total =  $db->table('tbl_audio_files as s')->where($whereCondition)->countAllResults();

            return $service->success(
                [
                    'message'       =>  Lang('Language.list_success'),
                    'data'          =>  [
                        'total'             =>  $total,
                        'AudioList'         =>  $appData,
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

    public function add()
    {
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

            'title' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required')
                ]
            ],

            // 'audio' => [
            //     'rules'         =>  'mimes:mp3',
            //     'errors'        => [
            //         // 'required'      =>  Lang('Language.required'),
            //         'mimes' => 'Only MP3 audio files are allowed.',
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

        try {

            $title     =  $this->request->getVar('title');
            $artist     =  $this->request->getVar('artist');
            $album     =  $this->request->getVar('album');
            $photo_url = '';
            $audio = '';

            if ($this->request->getFile('audio')) {
                if ($_FILES['audio']['type'] != 'audio/mp3' && $_FILES['audio']['type'] != 'audio/mpeg') {
                    return $service->fail(
                        [
                            'errors'     =>  "",
                            'message'   =>  Lang('Language.Only MP3 audio files are allowed.')
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }
                $file_path = 'public/assets/uploads/audios/';
                $audio  =  $this->request->getFile('audio');
                $tempname  = $audio->getRandomName();
                $audio->move($file_path, $tempname);
                $audio = $file_path . $tempname;
            }

            if ($this->request->getFile('image')) {
                $file_path = 'public/assets/uploads/audios_images/';
                $image  =  $this->request->getFile('image');
                $tempname  = $image->getRandomName();
                $image->move($file_path, $tempname);
                $photo_url = $file_path . $tempname;
            }

            $data = array(
                'title'          => $title,
                'audio'          => $audio,
                'image'          => $photo_url ? $photo_url : '',
                'artist'         => $artist ? $artist : 'Unknonw Artist',
                'album'          => $album ? $album : 'Unknonw Album',
                'created_at'     => date('Y-m-d H:i:s'),
            );

            $db = db_connect();
            $insert = $db->table('tbl_audio_files')->insert($data);
            if($insert) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.add_success'),
                        'data'          =>  $data
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

    public function view()
    {
        $audioModel      =  new AudioFiles();
        $service        =  new Services();
        $service->cors();

        $audio_id  =  $this->request->getVar('audio_id');

        $rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'audio_id' => [
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
            $audioDetails = $audioModel->where("id", $audio_id)->where("status !=",'2')->first();

            if(!empty($audioDetails)) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.details_success'),
                        'data'          =>  $audioDetails
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

    public function edit()
    {
        $audioModel      =  new AudioFiles();
        $service        =  new Services();
        $service->cors();

        $audio_id            =  $this->request->getVar('audio_id');
        $title     =  $this->request->getVar('title');
        $artist     =  $this->request->getVar('artist');
        $album     =  $this->request->getVar('album');
       

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
            'audio_id' => [
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
            // 'audio' => [
            //     'rules'         =>  'required|mimes:mp3',
            //     'errors'        => [
            //         'required'      =>  Lang('Language.required'),
            //         'mimes' => 'Only MP3 audio files are allowed.',
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

        try {
            $audioDetails = $audioModel->where("id", $audio_id)->where("status !=",'2')->first();
            if (empty($audioDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Audio Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }


            if ($this->request->getFile('audio')) {
                if ($_FILES['audio']['type'] != 'audio/mp3' && $_FILES['audio']['type'] != 'audio/mpeg') {
                    return $service->fail(
                        [
                            'errors'     =>  "",
                            'message'   =>  Lang('Language.Only MP3 audio files are allowed.')
                        ],
                        ResponseInterface::HTTP_BAD_REQUEST,
                        $this->response
                    );
                }
                $file_path = 'public/assets/uploads/audios/';
                $audio  =  $this->request->getFile('audio');
                $tempname  = $audio->getRandomName();
                $audio->move($file_path, $tempname);
                $audio = $file_path . $tempname;
            } else {
                $audio = $audioDetails['audio'];
            }

            if ($this->request->getFile('image')) {
                $file_path = 'public/assets/uploads/audios_images/';
                $image  =  $this->request->getFile('image');
                $tempname  = $image->getRandomName();
                $image->move($file_path, $tempname);
                $photo_url = $file_path . $tempname;
            } else {
                $photo_url = $audioDetails['image'];
            }

            $data = [
                'title'         => $title ? $title : $audioDetails['title'],
                'audio'         => $audio ? $audio : $audioDetails['audio'],
                'image'         => $photo_url ? $photo_url : $audioDetails['image'],
                'artist'        => $artist ? $artist : $audioDetails['artist'],
                'album'         => $album ? $album : $audioDetails['album'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $db = db_connect();
            $update = $db->table('tbl_audio_files')
                ->where('id', $audio_id)
                ->update($data);

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.update_success'),
                        'data'          =>  ""
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

    public function delete()
    {
        $audioModel      =  new AudioFiles();
        $service        =  new Services();
        $service->cors();

        $audio_id  =  $this->request->getVar('audio_id');

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
            'audio_id' => [
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
            $audioDetails = $audioModel->where("id", $audio_id)->where("status !=",'2')->first();
            if (empty($audioDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Audio Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $delete = $db->table('tbl_audio_files')
                ->where('id', $audio_id)
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

    public function changeStatus()
    {
        $audioModel      =  new AudioFiles();
        $service        =  new Services();
        $service->cors();

        $audio_id  =  $this->request->getVar('audio_id');
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
            'audio_id' => [
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
            $audioDetails = $audioModel->where("id", $audio_id)->where("status !=",'2')->first();
            if (empty($audioDetails)) {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Audio Not Found'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

            $db = db_connect();
            $update = $db->table('tbl_audio_files')
                ->where('id', $audio_id)
                ->set('status', $status)
                ->update();

            if($update) 
            {
                return $service->success([
                        'message'       =>  Lang('Language.Audio status changed successfully'),
                        'data'          =>  ""
                    ],
                    ResponseInterface::HTTP_CREATED,
                    $this->response
                );
            } else {
                return $service->fail(
                    [
                        'errors'    =>  "",
                        'message'   =>  Lang('Language.Unable to change Audio status, please try againn'),
                    ],
                    ResponseInterface::HTTP_BAD_REQUEST,
                    $this->response
                );
            }

        } catch (Exception $e) {
            return $service->fail(
                [
                    'errors'    =>  $e->getMessage(),
                    'message'   =>  Lang('Language.Unable to change Audio status, please try againn'),
                ],
                ResponseInterface::HTTP_BAD_REQUEST,
                $this->response
            );
        }
    }
}
