<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\OtaMoodel;
use App\Models\ProviderModel;
use App\Models\PackageModels;
use App\Models\MovmentModels;
use App\Models\ImagePackageModels;
use App\Models\VehicleModels;
use App\Models\ActivitieImgModel;
use App\Models\BookingModel;
use App\Models\ActivitieModel;
use App\Models\AccountModel;
use App\Models\Admin_transaction_Model;
use App\Models\OtaProviderAccountModel;
use App\Models\BookingPaymentRecordModel;
use App\Models\User_transaction_Model;
use App\Models\ServiceCommisionModel;
use App\Libraries\MailSender;
use App\Models\GuideModel;
use App\Models\GuideDocModel;
use App\Models\DayMappingModel;

use App\Models\UserModels;
use App\Models\ZiyaratPoints;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

use Exception;
use mysqli;

use Config\Services;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Admin extends ResourceController
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
		$lang = (isset($_POST) && !empty($_POST)) ? $_POST["language"] : '';
		if (!empty($lang)) {
			$language = \Config\Services::language();
			$language->setLocale($lang);
		} else {
			echo json_encode(['status' => 403, 'messages' => 'language required']);
			die();
		}

		// $str = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
		// if ($str != 'accessDefine') {
		// 	checkEmptyPost($_POST);
		// }

		$db = \Config\Database::connect();
		// Check Authentication
		$this->token = $token = $_POST['authorization'];
		$this->user_id = $user_id = $_POST['logged_user_id'];
		$this->user_role = $user_role = $_POST['logged_user_role'];

		// echo json_encode($decoded);die();

		if (!$this->service->getAccessForSignedUser($token, $user_role)) {
			echo json_encode(['status' => 'failed', 'messages' => 'Access denied', 'status_code' => '401']);
			die();
		}

		$timezone = "Asia/Kolkata";
		date_default_timezone_set($timezone);
	}

	private $user_excluded_keys = array("password", "token");
	private $access = array("id", "staff_member_id", "created_by", "created_date", "updated_date");

	use ResponseTrait;

	public function getKey()
	{
		return "my_application_secret";
	}

	public function adminDetails()
	{
		$AdminModel = new AdminModel();
		$key = $this->getKey();
		$authHeader = $this->request->getPost("authorization");
		$userid = $this->request->getPost("logged_user_id");
		$user_role =  $this->request->getPost("logged_user_role");

		if ($user_role == "admin") {
			$userdata = $AdminModel->where("id", $userid)->first();
			if (!empty($userdata)) {
				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang('Language.User details'),
					'info' => $userdata
				];
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang('Language.User Not found'),
					'info' => ''
				];
			}
			return $this->respondCreated($response);
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang('Language.User Role Not found'),
				'info' => ''
			];
		}
		return $this->respondCreated($response);
	}

	// Check Authintication
	public function checkAuthentication($token = '', $userid = '', $role = '')
	{
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$UserModels = new UserModels();
		$OtaMoodel = new OtaMoodel();
		$GuideModel = new GuideModel();

		$key = $this->getKey();
		try {
			$decoded = JWT::decode($token, $key, array("HS256"));
			if ($decoded) {
				$id = $decoded->id;
				if ($role == "admin") {
					$userdata = $AdminModel->where("token", $token)->where("id", $userid)->first();
				} elseif ($role == "provider") {
					$userdata = $ProviderModel->where("token", $token)->where("id", $userid)->first();
				} elseif ($role == "ota") {
					$userdata = $OtaMoodel->where("token", $token)->where("id", $userid)->first();
				} elseif ($role == "user") {
					$userdata = $UserModels->where("token", $token)->where("id", $userid)->first();
				}elseif ($role == "guide") {
					$userdata = $GuideModel->where("token", $token)->where("id", $userid)->first();
				}
				//  else {
				// 	$userdata = $Employee->where("token", $token)->where("id", $userid)->first();
				// }
				if (!empty($userdata)) {
					return true;
				} else {
					return false;
				}
			}
		} catch (Exception $ex) {
			return false;
		}
		return $token;
	}


	// // Update Info
	public function adminUpdate()
	{
		$AdminModel = new AdminModel();
		$user_id = $this->request->getPost("logged_user_id");
		$user_role = $this->request->getPost("logged_user_role");
		$info = $AdminModel->where('id', $user_id)->first();

		if (isset($_FILES) && !empty($_FILES)) {
			$file = $this->request->getFile('profile_pic');
			if ($file->isValid()) {
				$path = 'public/assets/uploads/admin/profiles/';
				$newName = $file->getRandomName();
				$file->move($path, $newName);
				$pic = $path . $newName;
			} else {
				// $info = $AdminModel->where('id', $user_id)->first();
				$pic = $info['profile_pic'];
			}
		} else {
			$pic = $info['profile_pic'];
		}
		$data = [
			"username" => $this->request->getPost("username"),
			"mobile" => $this->request->getPost("mobile"),
			"city" => $this->request->getPost("city"),
			"state" => $this->request->getPost("state"),
			"zip_code" => $this->request->getPost("zip_code"),
			"profile_pic" => $pic
		];
		if ($user_role == "admin") {
			$res = $AdminModel->update($user_id, $data);
			// $document = $file->move($pic);
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.User Role Not Found")
			];
		}
		if ($res) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang("Language.Updated Successfully")
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.Failed To Update")
			];
		}
		return $this->respondCreated($response);
	}


	// 13-01-2021
	public function passwordCheck()
	{
		$AdminModel = new AdminModel();
		// $InternalAdminModel = new InternalAdminModel();
		if ($this->user_role == 1) {
			$userdata = $AdminModel->where("token", $this->token)->where("id", $this->user_id)->where('status', 'active')->first();
		} 
		// else {
			// $userdata = $InternalAdminModel->where("token", $this->token)->where("id", $this->user_id)->where("user_role", $this->user_role)->where('status', 'active')->first();
		// }
		if (password_verify($this->request->getPost("password"), $userdata['password'])) {
			$response = [
				'status' => 'success',
				'messages' => 'access successfully'
			];
		} else {
			$response = [
				'status' => 'failed',
				'messages' => 'password worng'
			];
		}
		return $this->respondCreated($response);
	}


	public function resetPassword()
	{
		$emp = new UserModels();
		$wp = new ProviderModel();
		$user_role = $this->request->getPost('user_role');
		$user_id = $this->request->getPost('user_id');
		$newPassword = $this->request->getPost("new_password");
		$confirmPass = $this->request->getPost("confirm_password");
		if ($newPassword != $confirmPass) {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang('Language.Confirm password not match'),
			];
			return $this->respondCreated($response);
		}
		if ($user_role == 3) {
			$userdata = $wp->where("id", $user_id)->where("user_role", $user_role)->first();
			if (!empty($userdata)) {
				$res = $wp->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
				$response = [
					'status' => "success",
					'status_code' => 500,
					'messages' => lang('Language.password set successfully'),
				];
				return $this->respondCreated($response);
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang('Language.user not found'),
				];
				return $this->respondCreated($response);
			}
		} elseif ($user_role == 4) {
			$userdata = $emp->where("id", $user_id)->where("user_role", $user_role)->first();
			if (!empty($userdata)) {
				$res = $emp->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
				$response = [
					'status' => "success",
					'status_code' => 500,
					'messages' => lang('Language.password set successfully'),
				];
				return $this->respondCreated($response);
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang('Language.user not found'),
				];
				return $this->respondCreated($response);
			}
		}
	}

	//  Add provider  12/5/2022
	public function addProvider()
	{
		$email = \Config\Services::email();
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$ServiceCommisionModel = new ServiceCommisionModel();
		$password =  $this->request->getPost("password");


		// Email Validation
		$userdata = $ProviderModel->where("email", $this->request->getPost("email"))->first();
		if (!empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Already Exists')]);
			die();
		}

		$data = [
			"firstname" => $this->request->getPost("firstname"),
			"lastname" => $this->request->getPost("lastname"),
			"company_name" => $this->request->getPost("company_name"),
			"email" => $this->request->getPost("email"),
			"password" => password_hash($password, PASSWORD_DEFAULT),
			"mobile" => $this->request->getPost("mobile"),
			"gender" => $this->request->getPost("gender"),
			"supporter_no" => $this->request->getPost("supporter_no"),
			"user_role" => "provider",
			"created_by" => "admin"
			// "ipsc" => $this->request->getPost("ipsc"),
			// "bank_account" => $this->request->getPost("bank_account"),
			// "city" => $this->request->getPost("city"),
			// "state" => $this->request->getPost("state"),
			// "country" => $this->request->getPost("country"),
			// "zip_code" => $this->request->getPost("zip_code"),
			// "bank_name" => $this->request->getPost("bank_name"),
			// "branch_name" => $this->request->getPost("branch_name"),
		];
		
		if (!checkEmptyPost($data)) {
			
			$data['ipsc'] = $this->request->getPost("ipsc");
			$data['bank_account'] = $this->request->getPost("bank_account");
			$data['city'] = $this->request->getPost("city");
			$data['state'] = $this->request->getPost("state");
			$data['country'] = $this->request->getPost("country");
			$data['zip_code'] = $this->request->getPost("zip_code");
			
			// echo json_encode($data);die();

			if ($ProviderModel->insert($data)) {
				$last_provider_id = $ProviderModel->insertID;

				$service_json = $this->request->getPost("service_json");
				$service_mapping = json_decode($service_json, TRUE);
				// foreach ($vechiles['vechile'] as $vec => $values) {
				foreach ($service_mapping['service_details'] as $kk => $vall) {
					$service_id =  json_encode($vall['service_id']);
					$service_type = json_encode($vall['service_type']);
					$service_name = trim($service_type, '"');
					$user_id = $last_provider_id;
					$user_role = "provider";
					$commision_in_percent = json_encode($vall['commision_in_percent']);

					$vechiles_data = [
						'service_id' => $service_id,
						'service_type' => $service_name,
						'user_id' => $user_id,
						'user_role' => $user_role,
						'commision_in_percent' => $commision_in_percent
					];
					$insert_service = $ServiceCommisionModel->insert($vechiles_data);
				}

				// Send Email
				$usermail =  $data['email'];
				$password =  $password;
				$firstname =  $data['firstname'];
				// echo json_encode($data['email']);die();


				$data = array('email' => $usermail, 'password' => $password, 'username' => $firstname);
				$msg_template = view('emmail_templates/forgotpassword.php', $data);
				$subject      = 'Provider Email and Password';
				$to_email     =  $usermail;

				$abc =  MailSender::sendMail($to_email, $subject, $msg_template, '', '', "umarhaaddons", '');
				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Provider Create Successfully")
				];
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang("Language.Failed to Create")
				];
			}
			return $this->respondCreated($response);
		}
		// return $this->respondCreated($response);
	}

	// update provider by admin
	public function updateProbiderByAdmin()
	{
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$ServiceCommisionModel = new ServiceCommisionModel();
		$provider_id = $this->request->getPost("provider_id");

		// Email Validation
		$userdata = $ProviderModel->where('user_role', "provider")->where("id", $provider_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
			die();
		}

		$data = [
			"firstname" => $this->request->getPost("firstname"),
			"lastname" => $this->request->getPost("lastname"),
			"company_name" => $this->request->getPost("company_name"),
			"mobile" => $this->request->getPost("mobile"),
			"ipsc" => $this->request->getPost("ipsc"),
			"gender" => $this->request->getPost("gender"),
			"bank_account" => $this->request->getPost("bank_account"),
			"city" => $this->request->getPost("city"),
			"state" => $this->request->getPost("state"),
			"supporter_no" => $this->request->getPost("supporter_no"),
			"country" => $this->request->getPost("country"),
			"zip_code" => $this->request->getPost("zip_code"),
			"commision_percent" => $this->request->getPost("commision_percent"),
		];

			// "bank_name" => $this->request->getPost("bank_name"),
			// "branch_name" => $this->request->getPost("branch_name"),
		if (!checkEmptyPost($data)) {

			$data['bank_name'] = $this->request->getPost("bank_name");
			$data['branch_name'] = $this->request->getPost("branch_name");

			if ($ProviderModel->update($provider_id, $data)) {

				// get all services for provider and delete and update new.
				$oldServiceDelete = $ServiceCommisionModel->where('user_role', "provider")->where("user_id", $provider_id)->delete();

				$service_mapping = json_decode($this->request->getPost("service_details"), TRUE);
				foreach ($service_mapping['service_details'] as $val) {
					$service_id =  $val['service_id'];
					$service_type = $val['service_type'];
					$user_id = $provider_id;
					$user_role = "provider";
					$commision_in_percent = $val['commision_in_percent'];

					$service_data = [
						'service_id' => $service_id,
						'service_type' => $service_type,
						'user_id' => $user_id,
						'user_role' => $user_role,
						'commision_in_percent' => $commision_in_percent
					];
					// echo json_encode($service_data);die();
					$insert_service = $ServiceCommisionModel->insert($service_data);
				}

				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Provider Update Successfully")
				];
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang("Language.Failed to update")
				];
			}
		}
		return $this->respondCreated($response);
	}

	// get provider detail by id
	public function getproviderdetail()
	{
		// echo json_encode("hi");die();
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$provider_id = $this->request->getPost("provider_id");

		// Email Validation
		$userdata = $ProviderModel->where('user_role', "provider")->where("id", $provider_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		$db = \Config\Database::connect();
		$builder = $db->table('tbl_provider as tp');
		$builder->select('tp.*,c.name as countrie,s.name as states , ci.name as cities');
		$builder->join('countries as c', 'c.id = tp.country', 'left');
		$builder->join('states as s', 's.id = tp.state', 'left');
		$builder->join('cities as ci', 'ci.id = tp.city', 'left');
		$builder->where('tp.id', $provider_id);
		$builder->where('tp.user_role', 'provider');
		$userdata = $builder->get()->getRowArray();

		$query = $db->table('tbl_service_commision_mapping');
		$query->select('service_id, service_type, commision_in_percent');
		$query->where('user_id', $provider_id);
		$query->where('user_role', 'provider');
		$userdata['service_data'] = $query->get()->getResultArray();

		if (!empty($userdata)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.User Details'),
				'info' => $userdata,
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.User data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	// active inactive provider 
	public function activeinactiveprovider()
	{
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$provider_id = $this->request->getPost("provider_id");
		$status = $this->request->getPost("status");
		$user_role = $this->request->getPost("logged_user_role");

		// Email Validation
		$userdata = $ProviderModel->where('user_role', "provider")->where("id", $provider_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($user_role == "admin") {
			$userdata = $ProviderModel->where("id", $provider_id)->first();
			if (!empty($userdata)) {
				$status = ($status != "active") ? "inactive" : "active";
				$res = $ProviderModel->update($provider_id, ['status' => $status]);
				if ($res) {
					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.Provider status changed successfully'),
					];
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Something wrong'),
					];
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User Not Found')
				];
			}
			return $this->respondCreated($response);
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}
	}

	// delete provider by Javeriya Kauser
	public function deleteProvider()
	{
		$service   =  new Services();
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();

		$rules = [
            'language' => [
                'rules'         =>  'required|in_list[' . LANGUAGES . ']',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
                    'in_list'       =>  Lang('Language.in_list', [LANGUAGES]),
                ]
            ],
            'provider_id' => [
                'rules'         =>  'required',
                'errors'        => [
                    'required'      =>  Lang('Language.required'),
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

		$provider_id = $this->request->getPost("provider_id");
		$user_role = $this->request->getPost("logged_user_role");

		// Email Validation
		$userdata = $ProviderModel->where('user_role', "provider")->where("id", $provider_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($user_role == "admin") {
			$userdata = $ProviderModel->where("id", $provider_id)->first();
			if (!empty($userdata)) {
				$res = $ProviderModel->update($provider_id, ['status' => 'deleted']);
				// $res = $ProviderModel->where("id", $provider_id)->delete();
				if ($res) {
					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.Provider Deleted successfully'),
					];
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Something wrong'),
					];
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User Not Found')
				];
			}
			return $this->respondCreated($response);
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}
	}

	//  Add OTA  13/5/2022
	public function addOta()
	{
		$email = \Config\Services::email();
		$OtaMoodel = new OtaMoodel();
		$ServiceCommisionModel = new ServiceCommisionModel();
		$passwords = $this->request->getPost("password");

		// Email Validation
		$userdata = $OtaMoodel->where("email", $this->request->getPost("email"))->first();
		if (!empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Already Exists')]);
			die();
		}

		$data = [
			"firstname" => $this->request->getPost("firstname"),
			"lastname" => $this->request->getPost("lastname"),
			"company_name" => $this->request->getPost("company_name"),
			"email" => $this->request->getPost("email"),
			"plain_password" => $this->request->getPost("password"),
			"password" => password_hash($passwords, PASSWORD_DEFAULT),
			"mobile" => $this->request->getPost("mobile"),
			"domain_type" => $this->request->getPost("domain_type"),
			"domain_name" => $this->request->getPost("domain_name"),
			"ipsc" => $this->request->getPost("ipsc"),
			"gender" => $this->request->getPost("gender"),
			"bank_account" => $this->request->getPost("bank_account"),
			'supporter_no' => $this->request->getPost("supporter_no"),
			"city" => $this->request->getPost("city"),
			"state" => $this->request->getPost("state"),
			"country" => $this->request->getPost("country"),
			"zip_code" => $this->request->getPost("zip_code"),

			"bank_name" => $this->request->getPost("bank_name"),
			"branch_name" => $this->request->getPost("branch_name"),

			"user_role" => "ota",
			// "commision_percent" => $this->request->getPost("commision_percent"),
			"created_by" => "admin",
		];
		// echo json_encode($data);die();
		if (!checkEmptyPost($data)) {
			if ($OtaMoodel->insert($data)) {

				$last_ota_id = $OtaMoodel->insertID;

				$service_json = $this->request->getPost("service_json");
				$service_mapping = json_decode($service_json ?? '', TRUE);
				// foreach ($vechiles['vechile'] as $vec => $values) {
				if (!empty($service_mapping)) {
					foreach ($service_mapping['service_details'] as $kk => $vall) {
						$service_id =  json_encode($vall['service_id']);
						$service_type = json_encode($vall['service_type']);
						$service_name = trim($service_type, '"');
						$user_id = $last_ota_id;
						$user_role = "ota";
						$commision_in_percent = json_encode($vall['commision_in_percent']);

						$vechiles_data = [
							'service_id' => $service_id,
							'service_type' => $service_name,
							'user_id' => $user_id,
							'user_role' => $user_role,
							'commision_in_percent' => $commision_in_percent
						];
						$insert_service = $ServiceCommisionModel->insert($vechiles_data);
					}
				}
				// Send Email
				$mail = \Config\Services::email();
				$usermail =  $data['email'];
				$password =  $passwords;
				$firstname =  $data['firstname'];




				$data = array('email' => $usermail, 'password' => $password, 'username' => $firstname);
				$msg_template = view('emmail_templates/forgotpassword.php', $data);
				$subject      = 'OTA Email and Password';
				$to_email     =  $usermail;

				$abc =  MailSender::sendMail($to_email, $subject, $msg_template, '', '', "umarhaaddons", '');
				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.OTA Create Successfully")
				];
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang("Language.Failed to Create")
				];
			}
			return $this->respondCreated($response);
		}
	}

	// update ota by admin
	public function updateOtaByAdmin()
	{
		$OtaMoodel = new OtaMoodel();
		$AdminModel = new AdminModel();
		$ServiceCommisionModel = new ServiceCommisionModel();
		$ota_id = $this->request->getPost("ota_id");

		// Email Validation
		$userdata = $OtaMoodel->where('user_role', "ota")->where("id", $ota_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		$data = [
			"firstname" => $this->request->getPost("firstname"),
			"lastname" => $this->request->getPost("lastname"),
			"company_name" => $this->request->getPost("company_name"),
			"mobile" => $this->request->getPost("mobile"),
			"ipsc" => $this->request->getPost("ipsc"),
			"gender" => $this->request->getPost("gender"),
			"bank_account" => $this->request->getPost("bank_account"),
			"city" => $this->request->getPost("city"),
			"state" => $this->request->getPost("state"),
			"country" => $this->request->getPost("country"),
			"zip_code" => $this->request->getPost("zip_code"),
			"commision_percent" => $this->request->getPost("commision_percent"),
			"domain_name" => $this->request->getPost("domain_name"),
			"domain_type" => $this->request->getPost("domain_type"),
			'supporter_no' => $this->request->getPost("supporter_no"),
			'supporter_email' => $this->request->getPost("supporter_email"),
			'website_link' => $this->request->getPost("website_link"),
			'facebook_link' => $this->request->getPost("facebook_link"),

			"bank_name" => $this->request->getPost("bank_name"),
			"branch_name" => $this->request->getPost("branch_name"),
		];
		if (!checkEmptyPost($data)) {
			if ($OtaMoodel->update($ota_id, $data)) 
			{
				// get all services for provider and delete and update new.
				$oldServiceDelete = $ServiceCommisionModel->where('user_role', "ota")->where("user_id", $ota_id)->delete();

				$service_mapping = json_decode($this->request->getPost("service_details"), TRUE);
				foreach ($service_mapping['service_details'] as $val) {
					$service_id =  $val['service_id'];
					$service_type = $val['service_type'];
					$user_id = $ota_id;
					$user_role = "ota";
					$commision_in_percent = $val['commision_in_percent'];

					$service_data = [
						'service_id' => $service_id,
						'service_type' => $service_type,
						'user_id' => $user_id,
						'user_role' => $user_role,
						'commision_in_percent' => $commision_in_percent
					];
					// echo json_encode($service_data);die();
					$insert_service = $ServiceCommisionModel->insert($service_data);
				}

				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Ota Update Successfully")
				];
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 500,
					'messages' => lang("Language.Failed to update")
				];
			}
		}
		return $this->respondCreated($response);
	}

	// get OTA detail by id
	public function getOtaDetail()
	{
		$OtaMoodel = new OtaMoodel();
		$AdminModel = new AdminModel();
		$ota_id = $this->request->getPost("ota_id");

		// Email Validation
		$userdata = $OtaMoodel->where('user_role', "ota")->where("id", $ota_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		$db = \Config\Database::connect();
		$builder = $db->table('tbl_ota as to');
		$builder->select('to.*,c.name as countrie,s.name as states , ci.name as cities');
		$builder->where('to.id', $ota_id);
		$builder->where('to.user_role', 'ota');
		$builder->join('countries as c', 'c.id = to.country', 'left');
		$builder->join('states as s', 's.id = to.state', 'left');
		$builder->join('cities as ci', 'ci.id = to.city', 'left');
		$userdata = $builder->get()->getRowArray();

		$query = $db->table('tbl_service_commision_mapping');
		$query->select('service_id, service_type, commision_in_percent');
		$query->where('user_id', $ota_id);
		$query->where('user_role', 'ota');
		$userdata['service_data'] = $query->get()->getResultArray();
		if (!empty($userdata)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.User Details'),
				'info' => $userdata,
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.User data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	// active inactive OTA
	public function activeInactiveOta()
	{
		$OtaMoodel = new OtaMoodel();
		$ota_id = $this->request->getPost("ota_id");
		$status = $this->request->getPost("status");
		$user_role = $this->request->getPost("logged_user_role");
		// Email Validation
		$userdata = $OtaMoodel->where('user_role', "ota")->where("id", $ota_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($user_role == "admin") {
			$userdata = $OtaMoodel->where("id", $ota_id)->first();
			if (!empty($userdata)) {
				$status = ($status != "active") ? "inactive" : "active";
				$res = $OtaMoodel->update($ota_id, ['status' => $status]);
				if ($res) {
					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.OTA status changed successfully'),
					];
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Something wrong'),
					];
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User Not Found')
				];
			}
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}

		return $this->respondCreated($response);
	}

	// get User detail by id
	public function getUserDetail()
	{
		$UserModels = new UserModels();
		$AdminModel = new AdminModel();
		$user_id = $this->request->getPost("user_id");

		// Email Validation
		$userdata = $UserModels->where('user_role', "user")->where("id", $user_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		$db = \Config\Database::connect();
		$builder = $db->table('tbl_user as tu');
		$builder->select('tu.*,c.name as countrie,s.name as states , ci.name as cities');
		$builder->where('tu.id', $user_id);
		$builder->where('tu.user_role', 'user');
		$builder->join('countries as c', 'c.id = tu.country', 'left');
		$builder->join('states as s', 's.id = tu.state', 'left');
		$builder->join('cities as ci', 'ci.id = tu.city', 'left');
		$userdata = $builder->get()->getRowArray();

		// Add BY RIZ
		$bookings = $db->table('meals_booking as b')
              ->select('b.*, m.title as meal_name, b.booking_status, CONCAT(u.firstname," ",u.lastname) as user_name, CONCAT(pro.firstname," ",pro.lastname) as provider_name, CONCAT(o.firstname," ",o.lastname) as ota_name')
              ->join('tbl_meals as m','m.id = b.meals_id')
              ->join('tbl_user as u','u.id = b.user_id')
              ->join('tbl_provider as pro','pro.id = b.provider_id')
              ->join('tbl_ota as o','o.id = b.ota_id')
			//   ->join('tbl_vehicle_master as v','v.id = b.cars')
              ->where('b.user_id',$user_id)
              ->orderBy('b.id', 'DESC')
              ->get()->getResult();
		// EnD

		if (!empty($userdata)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.User Details'),
				'info' => $userdata,
				'package_bookings' => $bookings,
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.User data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	// active inactive user/customer
	public function activeInactiveUSer()
	{
		$UserModels = new UserModels();
		$user_id = $this->request->getPost("user_id");
		$status = $this->request->getPost("status");
		$user_role = $this->request->getPost("logged_user_role");
		// Email Validation
		$userdata = $UserModels->where('user_role', "user")->where("id", $user_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($user_role == "admin") {
			$userdata = $UserModels->where("id", $user_id)->first();
			if (!empty($userdata)) {
				$status = ($status != "active") ? "inactive" : "active";
				$res = $UserModels->update($user_id, ['status' => $status]);
				if ($res) {
					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.User status changed successfully'),
					];
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Something wrong'),
					];
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User Not Found')
				];
			}
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}

		return $this->respondCreated($response);
	}

	// active inactive user/package
	public function activeInactivePackage()
	{
		$PackageModels = new PackageModels();
		$package_id = $this->request->getPost("package_id");
		$status = $this->request->getPost("status");
		$user_role = $this->request->getPost("logged_user_role");
		// Email Validation
		$Packagerdata = $PackageModels->where("id", $package_id)->first();
		if (empty($Packagerdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		if ($user_role == "admin") {
			$Packagerdata = $PackageModels->where("id", $package_id)->first();
			if (!empty($Packagerdata)) {
				$status = ($status != "active") ? "inactive" : "active";
				$res = $PackageModels->update($package_id, ['status_by_admin' => $status]);
				if ($res) {
					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.Package status changed successfully'),
					];
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Something wrong'),
					];
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Package Not Found')
				];
			}
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}

		return $this->respondCreated($response);
	}

	// View detail of of package  by admin
	public function getPackageDetail()
	{
		$PackageModels = new PackageModels();
		$package_id = $this->request->getPost("package_id");

		// Email Validation
		$userdata = $PackageModels->where("id", $package_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		$db = \Config\Database::connect();
		$builder = $db->table('tbl_package as tu');
		$builder->select('tu.*,a.*, b.*, c.*');
		$builder->where('tu.id', $package_id);
		$builder->join('tbl_package_image as a', 'a.package_id = tu.id', 'left', 'limits = 0 10');
		$builder->join('tbl_package_movment as b', 'b.package_id = tu.id', 'left');
		$builder->join('tbl_package_vehicle as c', 'c.package_id = tu.id', 'left');
		$userdata = $builder->get()->getRowArray();
		if (!empty($userdata)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.User Details'),
				'info' => $userdata,
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.User data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	// update customer by admin
	public function updateCustomerByAdmin()
	{
		$UserModels = new UserModels();
		$user_id = $this->request->getPost("logged_user_id");
		$user_role = $this->request->getPost("logged_user_role");
		$client_id = $this->request->getPost("client_id");


		// Email Validation
		$userdata = $UserModels->where("id", $client_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}
		$data = [
			"firstname" => $this->request->getPost("firstname"),
			"lastname" => $this->request->getPost("lastname"),
			"mobile" => $this->request->getPost("mobile"),
			"gender" => $this->request->getPost("gender"),
			"city" => $this->request->getPost("city"),
			"state" => $this->request->getPost("state"),
			"country" => $this->request->getPost("country"),
			"zip_code" => $this->request->getPost("zip_code"),
		];
		if (!empty($userdata)) {
			$res = $UserModels->update($client_id, $data);
			// $document = $file->move($pic);
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.User Not Found")
			];
		}
		if ($res) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang("Language.Updated Successfully")
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.Failed To Update")
			];
		}
		return $this->respondCreated($response);
	}

	// detail of all 
	public function allDetailsBySelf()
	{
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$OtaMoodel = new OtaMoodel();
		$UserModels = new UserModels();
		$GuideModel = new GuideModel();

		$logged_user_role = $this->request->getPost("logged_user_role");
		$user_id = $this->request->getPost("logged_user_id");

		// // Email Validation

		// if ($logged_user_role == "admin") {
		// 	$userdata = $AdminModel->where("id", $user_id)->first();
		// } elseif ($logged_user_role == "provider") {
		// 	$userdata = $ProviderModel->where("id", $user_id)->first();
		// } elseif ($logged_user_role == "ota") {
		// 	$userdata = $OtaMoodel->where("id", $user_id)->first();
		// } elseif ($logged_user_role == "user") {
		// 	$userdata = $UserModels->where("id", $user_id)->first();
		// }


		if ($logged_user_role == "provider") {
			$userdata = $ProviderModel->where("id", $user_id)->where("status", 'active')->first();
			if (empty($userdata)) {
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.provider Not Found')]);
				die();
			} else {
				$db = \Config\Database::connect();
				$builder = $db->table('tbl_provider as tp');
				$builder->select('tp.*,c.name as countrie,s.name as states , ci.name as cities');
				$builder->where('tp.id', $user_id);
				$builder->where('tp.user_role', 'provider');
				$builder->join('countries as c', 'c.id = tp.country', 'left');
				$builder->join('states as s', 's.id = tp.state', 'left');
				$builder->join('cities as ci', 'ci.id = tp.city', 'left');
				$userdata1 = $builder->get()->getRowArray();
			}

			$db = \Config\Database::connect();
			$query = $db->table('tbl_service_commision_mapping');
			$query->select('service_id, service_type, commision_in_percent');
			$query->where('user_id', $userdata1['id']);
			$query->where('user_role', 'provider');
			$provider_data= $query->get()->getResultArray();
			$userdata1['service_allowed'] = $provider_data;

		} elseif ($logged_user_role == "ota") {
			$userdata = $OtaMoodel->where("id", $user_id)->where("status", 'active')->first();
			if (empty($userdata)) {
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.ota Not Found')]);
				die();
			} else {
				$db = \Config\Database::connect();
				$builder = $db->table('tbl_ota as tp');
				$builder->select('tp.*,c.name as countrie,s.name as states , ci.name as cities');
				$builder->where('tp.id', $user_id);
				$builder->where('tp.user_role', 'ota');
				$builder->join('countries as c', 'c.id = tp.country', 'left');
				$builder->join('states as s', 's.id = tp.state', 'left');
				$builder->join('cities as ci', 'ci.id = tp.city', 'left');
				$userdata1 = $builder->get()->getRowArray();

				$query = $db->table('tbl_service_commision_mapping');
				$query->select('service_id, service_type, commision_in_percent');
				$query->where('user_id', $userdata1['id']);
				$query->where('user_role', 'ota');
				$provider_data = $query->get()->getResultArray();
				$userdata1['service_allowed'] = $provider_data;
				// echo json_encode($userdata1);die();
			}
		} elseif ($logged_user_role == "user") {
			$userdata = $UserModels->where("id", $user_id)->where("status", 'active')->first();
			if (empty($userdata)) {
				// echo json_encode("hi");die();
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.user Not Found')]);
				die();
			} else {
				$db = \Config\Database::connect();
				$builder = $db->table('tbl_user as tp');
				$builder->select('tp.*,c.name as countrie,s.name as states , ci.name as cities');
				$builder->where('tp.id', $user_id);
				$builder->where('tp.user_role', 'user');
				$builder->join('countries as c', 'c.id = tp.country', 'left');
				$builder->join('states as s', 's.id = tp.state', 'left');
				$builder->join('cities as ci', 'ci.id = tp.city', 'left');
				$userdata1 = $builder->get()->getRowArray();
			}
		} elseif($logged_user_role == "guide"){
			$userdata = $GuideModel->where("id", $user_id)->where("status", 'active')->first();
			if (empty($userdata)) {
				// echo json_encode("hi");die();
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.user Not Found')]);
				die();
			} else {
				$db = \Config\Database::connect();
				$builder = $db->table('tbl_guide as tp');
				$builder->select('tp.*');
				$builder->where('tp.id', $user_id);
				$userdata1 = $builder->get()->getRowArray();
			}
		}
		else {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Role Not Found')]);
			die();
		}

		if (!empty($userdata)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.Guide Details'),
				'info' => $userdata1,
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.Guide data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	// common api for get package images
	// get package images 
	public function getPackageImageForAll()
	{
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$OtaMoodel = new OtaMoodel();
		$UserModels = new UserModels();
		$active = 'active';

		$logged_user_role = $this->request->getPost("logged_user_role");
		$user_id = $this->request->getPost("logged_user_id");
		$package_id = $this->request->getPost("package_id");

		// check user
		$userdata = $UserModels->where("id", $user_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		// check provider
		$Providerdata = $ProviderModel->where("id", $user_id)->first();
		if (empty($Providerdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Provider Not Found')]);
			die();
		}

		// Email Validation
		$packagedata = $ProviderModel->where("id", $package_id)->where("status_by_admin", $active)->where("status", $active)->first();
		if (empty($packagedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}
	}


	// View detail of of package  by admin
	public function getPackageDetailsByAdmin()
	{
		$PackageModels = new PackageModels();
		$MovmentModels = new MovmentModels();
		$ImagePackageModels = new ImagePackageModels();
		$VehicleModels = new VehicleModels();
		$DayMappingModel = new DayMappingModel();
		$ZiyaratPoints = new ZiyaratPoints();

		$logged_user_role = $this->request->getPost("logged_user_role");
		$user_id = $this->request->getPost("logged_user_id");
		$package_id = $this->request->getPost("package_id");

		// Email Validation
		$userdata = $PackageModels->where("id", $package_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		$packagedata = $PackageModels->where("id", $package_id)->first();
		if (!empty($userdata) && !empty($packagedata)) {
			$image_data =  $ImagePackageModels->where("package_id", $package_id)->findAll();
			$points = explode(',',$packagedata['ziyarat_points']);
			$ziyarat_points = $ZiyaratPoints->whereIn('id',$points)->select('id, title_en, name_en')->findAll();

			$db = \Config\Database::connect();

			$builder = $db->table('tbl_package_vehicle as pv');
            $builder->select('pv.*');

			if ($packagedata['package_type'] == "group") {
				$builder->join('tbl_pax_master as pax', 'pax.id  = pv.no_of_pox_id');
				$builder->join('tbl_vehicle_master as vech', 'vech.id  = pv.vehicle_id');
				$builder->select('pax.name as pax_name,vech.name as vehicle_name');
			}
           
            $builder->where('pv.package_id', $package_id);
            $builder->where('pv.status', 'active');
            $Vehicle_data = $builder->get()->getResult();
			
			$Movment_data =  $MovmentModels->where("package_id", $package_id)->findAll();
            foreach($Movment_data as $key => $value)
            {
                $inventatory_detail = $DayMappingModel->where('movement_id',$value['id'])->where('package_id',$value['package_id'])->findAll();
                $Movment_data[$key]['inventatory_detail'] = $inventatory_detail;
            }

			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.Package Details'),
				'Package_data' => $packagedata,
				'Image_data' => $image_data,
				'Vehicle_data' => $Vehicle_data,
				'Movment_data' => $Movment_data,
				'ziyarat_points' => $ziyarat_points,
			];
		} else {

			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang('Language.User data not found'),
			];
		}
		return $this->respondCreated($response);
	}

	public function tryjson()
	{
		$ActivitieImgModel = new ActivitieImgModel();
		$jsonfile = $this->request->getFile("jsonfile");
		print_r($jsonfile);
	}

	// Otp Gentrator    
	public function generateTransaction($n)
	{
		$generator = "135792468";
		$result = "";
		for ($i = 1; $i <= $n; $i++) {
			$result .= substr($generator, (rand() % (strlen($generator))), 1);
		}
		return $result;
	}

	public function acceptPayment()
	{
		helper('text');
		$PackageModels = new PackageModels();
		$UserModels = new UserModels();
		$ProviderModel = new ProviderModel();
		$BookingModel = new BookingModel();
		$ActivitieModel = new ActivitieModel();
		$AccountModel = new AccountModel();
		$Admin_transaction_Model = new Admin_transaction_Model();
		$BookingPaymentRecordModel = new BookingPaymentRecordModel();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
		$User_transaction_Model = new User_transaction_Model();



		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$service_type = $this->request->getPost("service_type");
		$service_id = $this->request->getPost("service_id");
		$user_id = $this->request->getPost("user_id");
		$rate = $this->request->getPost("rate");
		$no_of_pox = $this->request->getPost("no_of_pox");
		$booking_id = $this->request->getFile("booking_id");


		// check user
		$userdata = $UserModels->where("id", $user_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($service_type == "package") {
			// check Package 
			$PackageData = $PackageModels->where("id", $service_id)->first();
			if (empty($PackageData)) {
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
				die();
			}
		} else {
			$ActivitieData = $ActivitieModel->where("id", $service_id)->first();
			if (empty($ActivitieData)) {
				echo json_encode(['status' => 'failed', 'messages' => lang('Language.Activitie Not Found')]);
				die();
			}
		}

		if ($service_type == "package") {

			// Check Booking detail with mandatory data
			$Bookingdata = $BookingModel->where('id', $booking_id)->where("service_type", $service_type)->where("service_id", $service_id)->where("user_id", $user_id)->where("no_of_pox", $no_of_pox)->where("rate", $rate)->where("action", 'confirm')->where("payment_status", 'pending')->first();
			// echo json_encode($Bookingdata);die();

			if (!empty($Bookingdata)) {
				$booking_id = $Bookingdata['id'];
				$account_data = $AccountModel->where('id', '1')->first();
				$old_balance = $account_data['amount'];
				$provider_id = $Bookingdata['provider_id'];
				$provider_data = $ProviderModel->select('commision_percent')->where("id", $provider_id)->first();
				$admin_commision = $provider_data['commision_percent'];

				// commision amount of admin				
				$commision_amount_admin = $rate / $admin_commision;
				$commision_amount_provider = $rate - $commision_amount_admin;
				// echo json_encode($commision_amount_provider);
				// die();
				// echo json_encode($account_data);die();
				$confirm_payment = ['payment_status' => 'completed'];
				if ($BookingModel->update($booking_id, $confirm_payment)) {

					$booking_payment_record = [
						'service_type' => $service_type,
						'sevice_id' => $service_id,
						'booking_id' => $booking_id,
						'user_id' => $user_id,
						'Provider_id' => $provider_id,
						'package_rate' => $rate,
						'admin_commision' => $admin_commision,
						'admin_amount' => $commision_amount_admin,
						'provider_amount' => $commision_amount_provider,
					];
					$BookingPaymentRecordModel->insert($booking_payment_record);
					$check_provider = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $provider_id)->first();
					if (empty($check_provider)) {
						$provider_amount = [
							'user_role' => 'provider',
							'user_id' => $provider_id,
							'total_amount' => $commision_amount_provider,
							'pending_amount' => $commision_amount_provider,
						];
						$OtaProviderAccountModel->insert($provider_amount);
					} else {
						$provider_account_id = $check_provider['id'];
						$pervious_total_amount = $check_provider['total_amount'];
						$pervious_pending_amount = $check_provider['pending_amount'];

						$update_provier_amount = [
							'total_amount' => $pervious_total_amount + $commision_amount_provider,
							'pending_amount' => $pervious_pending_amount + $commision_amount_provider,
						];

						$OtaProviderAccountModel->update($provider_account_id, $update_provier_amount);
					}

					$admin_transaction = [
						'admin_id' => $logged_user_id,
						'user_id' => $user_id,
						'user_type' => 'user',
						'transaction_type' => 'Cr',
						'service_type' => $service_type,
						'service_id' => $service_id,
						'transaction_reason' => "Credit Amount of " . $service_type . " Of User",
						'account_id' => 1,
						'old_balance' => $old_balance,
						'transaction_amount' => $rate,
						'current_balance' => $old_balance + $rate,
						'transaction_id' => generateRandomString('TRANSACTION'),
						'transaction_status' => 'success',
						'transaction_date' => date("Y-m-d")
					];
					$transaction_id = $admin_transaction['transaction_id'];
					$Admin_transaction_Model->insert($admin_transaction);
					// update  Admin Account
					$admin_account = [
						'amount' => $old_balance + $rate
					];
					$AccountModel->update('1', $admin_account);
					$user_transaction = [
						'customer_id' => $user_id,
						'user_id' => $logged_user_id,
						'user_type' => 'admin',
						'transaction_type' => 'Dr',
						'transaction_reason' => 'Package Amount to Admin',
						'transaction_amount' => $rate,
						'transaction_id' => $transaction_id,
						'transaction_status' => 'success',
						'transaction_date' => date("Y-m-d"),
						'service_type' => $service_type,
						'service_id' => $service_id
					];
					$User_transaction_Model->insert($user_transaction);
					$response = [
						'status' => "success",
						'status_code' => 200,
						'messages' => lang('Language.Payment Accept')
					];
				}
			} else {
			}
		} elseif ($service_type == "activitie") {
		} else {
		}
		return $this->respondCreated($response);
	}

	public function guideDetail()
	{
		$GuideModel = new GuideModel();
		$GuideDocModel = new GuideDocModel();

		$db = \Config\Database::connect();

		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$guide_id = $this->request->getPost("guide_id");

		// check Guide
		$userdata = $GuideModel->where("id", $guide_id)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}

		if ($logged_user_role == 'admin') {
			$userdata = $GuideModel->where("id", $guide_id)->first();
			$guidedoc = $GuideDocModel->select('id,guide_doc')->where("guide_id", $guide_id)->findAll();

			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.Guide Detail'),
				'info' => $userdata,
				'guide_doc' => $guidedoc
			];
		} else {
			$response = [
				'status' => "Failed",
				'status_code' => 500,
				'messages' => lang("Language.This Api is for admin only")
			];
		}
		return $this->respondCreated($response);
	}

	// active inactive Guide by admin
	public function activeInactiveGuide()
	{
		$GuideModel = new GuideModel();
		// $GuideDocModel = new GuideDocModel();
		$guide_id = $this->request->getPost("guide_id");
		$status = $this->request->getPost("status");
		$user_role = $this->request->getPost("logged_user_role");
		// Email Validation
		$guidedata = $GuideModel->where("id", $guide_id)->first();
		if (empty($guidedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Guide Not Found')]);
			die();
		}

		if ($user_role == "admin") {

			$status = ($status != "active") ? "inactive" : "active";
			$res = $GuideModel->update($guide_id, ['status' => $status]);
			if ($res) {
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Guide status changed successfully'),
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Something wrong'),
				];
			}
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not Found')
			];
		}
		return $this->respondCreated($response);
	}
	
} // class end

/* End of file Admin.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Admin.php */