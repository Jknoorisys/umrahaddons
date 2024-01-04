<?php

namespace App\Controllers;

use App\Models\ProviderModel;
use App\Models\AdminModel;
use App\Models\OtaMoodel;
use App\Models\UserModels;
use App\Models\GuideModel;
use App\Models\GuideDocModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
// use \Firebase\JWT\JWT;
use Exception;

use App\Libraries\MailSender;

use Config\Services;
use Firebase\JWT\JWT;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Auth extends ResourceController
{

	use ResponseTrait;
	private $service;

	public function __construct()
	{
		$this->service  = new Services();
		$this->service->cors();

		helper('auth');
		helper('notifications');
		$lang = $_POST["language"];
		if (!empty($lang)) {
			$language = \Config\Services::language();
			$language->setLocale($lang);
		} else {
			echo json_encode(['status' => 403, 'message' => 'language required']);
			die();
		}

		checkEmptyPost($_POST);

		// if(isset($_POST)) 
		// {
		// 	//Check empty field
		//      foreach($_POST as $key => $value) 
		//      { if(trim($value) == ""){ $this->errors[] = ucwords(str_replace('_', ' ', $key));}}
		//    	if(!empty($this->errors)) { echo json_encode(array('response'=>'failed', 'message'=>'Empty field(s) - '.implode(', ', $this->errors), 'response_code'=>100));die(); }
		// }		
	}

	private $user_excluded_keys = array("password", "token", "otp");

	public function getKey()
	{
		return "my_application_secret";
	}

	// Admin 
	public function admin()
	{
		$AdminModel = new AdminModel();
		$user_role = $this->request->getPost("user_role");
		// echo json_encode($user_role);die();

		if ($user_role == "admin") {
			$userdata = $AdminModel->where("email", $this->request->getPost("email"))->first();
			if (!empty($userdata)) {
				if (password_verify($this->request->getPost("password"), $userdata['password'])) {
					$key = $this->getKey();
					$payload = array(
						"role" => 'Admin',
						"id" => $userdata['id'],
						"date" => date('Y-m-d'),
					);
					// $token = JWT::encode($payload, $key);

					$token = $this->service->getSignedAccessTokenForUser('admin', $userdata['id']);

					// echo $token; exit;
					$AdminModel->update($userdata['id'], ['token' => $token]);

					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.User logged In Successfully'),
						'user_id' => $userdata['id'],
						'user_role' => $user_role,
						'token' => $token
					];
					// return $this->respondCreated($response);
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Incorrect Details')
					];
					// return $this->respondCreated($response);
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User Not found')
				];
				// return $this->respondCreated($response);
			}
		} else {
			$response = [
				'status' => 'failed',
				'status_code' => 500,
				'messages' => lang('Language.User Role Not found')
			];
		}
		return $this->respondCreated($response);
	}

	// Admin Logout
	public function logOut()
	{
		$AdminModel = new AdminModel();
		$key = $this->getKey();
		$token = $this->request->getPost("authorization");
		$userid = $this->request->getPost("logged_user_id");
		$user_role = $this->request->getPost("logged_user_role");
		if ($user_role == "admin") {

			if (!$this->service->getAccessForSignedUser($token, $user_role)) {
				echo json_encode(['status' => 401, 'message' => 'Access denied']);
				die();
			}
			$info = $AdminModel->where("id", $userid)->first();
			if (!empty($info)) {
				$res = $AdminModel->update($info['id'], ['token' => '']);
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Logged Out Successfully')
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Failed to logout')
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

	// Check Authintication
	public function checkAuthentication($token = '', $userid = '', $model = '')
	{
		// $CustomerModel = new CustomerModel();
		$AdminModel = new AdminModel();
		// $InternalAdminModel = new InternalAdminModel();
		// $workingpartner = new workingpartner();
		// $Employee = new Employee();
		// $UsersModel = new UsersModel();
		$key = $this->getKey();
		try {
			$decoded = JWT::decode($token, $key, array("HS256"));
			if ($decoded) {
				$id = $decoded->id;
				$userdata = $AdminModel->where("token", $token)->where("id", $userid)->first();
				if (!empty($userdata)) {
					return true;
				} else {
					return false;
				}
			}
		} catch (Exception $ex) {
			return false;
		}
		return true;
	}

	// GENERATE OTP FUNCTION
	public function generateNumericOTP($n)
	{
		$generator = "135792468";
		$result = "";
		for ($i = 1; $i <= $n; $i++) {
			$result .= substr($generator, (rand() % (strlen($generator))), 1);
		}
		return $result;
	}
	// 29-12-2020
	public function forgotPassword()
	{
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$OtaMoodel = new OtaMoodel();
		$UserModels = new UserModels();
		$GuideModel = new GuideModel();

		$mail = \Config\Services::email();
		$email = $this->request->getPost('email');
		$user_role = $this->request->getPost('user_role');

		if ($user_role == 'admin') {
			$userdata = $AdminModel->where("email", $email)->where("status", "active")->first();
		} elseif ($user_role == "provider") {
			$userdata = $ProviderModel->where("email", $email)->where("status", "active")->first();
		} elseif ($user_role == "ota") {
			$userdata = $OtaMoodel->where("email", $email)->where("status", "active")->first();
		} elseif ($user_role == "guide") {
			$userdata = $GuideModel->where("email", $email)->where("status", "active")->first();
		} else {
			$userdata = $UserModels->where("email", $email)->where("status", "active")->first();
		}

		if (!empty($userdata)) {
			$password = $this->generateNumericOTP(6);
			$hashPassword = password_hash($password, PASSWORD_DEFAULT);

			if ($user_role == "provider") {
				$res = $ProviderModel->update($userdata['id'], ['password' => $hashPassword]);
			} elseif ($user_role == "ota") {
				$res = $OtaMoodel->update($userdata['id'], ['password' => $hashPassword]);
			} elseif ($user_role == "guide") {
				$res = $GuideModel->update($userdata['id'], ['password' => $hashPassword]);
			} else {
				$res = $UserModels->update($userdata['id'], ['password' => $hashPassword]);
			}
			if ($res) {
				// Send Email
				// $mail->setFrom('noori.developer@gmail.com', 'Umrah Plus');
				// $mail->setTo($this->request->getPost("email"));
				// $data = array('email' => $userdata['email'], 'password' => $password, 'username' => $userdata['firstname']);
				// $msg = view('emmail_templates/forgotpassword.php', $data);
				// $mail->setSubject('Forgot Password');
				// $mail->setMessage($msg);
				// $mail->send();

				$data = array('email' => $userdata['email'], 'password' => $password, 'username' => $userdata['firstname']);
				$msg_template = view('emmail_templates/forgotpassword.php', $data);
				$subject      = 'Forgot Password';
				$to_email     =  $this->request->getPost("email"); // provider email
				$filename = "";
				$send     = sendEmail($to_email, $subject, $msg_template,$filename);
				$response = [
					'status' => 'success',
					'messages' => lang('Language.Password send to registered email')
				];
			} else {
				$response = [
					'status' => 'failed',
					'messages' => lang('Language.Something wrong')
				];
			}
		} else {
			$response = [
				'status' => 'failed',
				'messages' => lang('Language.User Not Found')
			];
		}
		return $this->respondCreated($response);
	}

	// all login  provider  ota users
	public function allLogin()
	{
		$ProviderModel = new ProviderModel();
		$UserModels = new UserModels();
		$OtaMoodel = new OtaMoodel();
		$GuideModel = new GuideModel();
		$GuideDocModel = new GuideDocModel();

		$user_role = $this->request->getPost("user_role");
		
		$device_token = $this->request->getPost('device_token');
        $device_type = $this->request->getPost('device_type');
		// echo json_encode($user_role);die();

		if ($user_role == "provider") {
			$userdata = $ProviderModel->where("email", $this->request->getPost("email"))->where('status','active')->first();
			if (!empty($userdata)) {
				if (password_verify($this->request->getPost("password"), $userdata['password'])) {
					$key = $this->getKey();
					$payload = array(
						"role" => 'Provider',
						"id" => $userdata['id'],
						"date" => date('Y-m-d'),
					);
					// $token = JWT::encode($payload, $key);

					$token = $this->service->getSignedAccessTokenForUser('provider', $userdata['id']);

					$ProviderModel->update($userdata['id'], ['token' => $token, 'device_type'=>$device_type, 'device_token'=>$device_token]);

					$db = \Config\Database::connect();
					$query = $db->table('tbl_service_commision_mapping');
					$query->select('service_id, service_type, commision_in_percent');
					$query->where('user_id', $userdata['id']);
					$query->where('user_role', 'provider');
					$provider_data= $query->get()->getResultArray();

					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.Provider Logged In Successfully'),
						'user_id' => $userdata['id'],
						'user_role' => $user_role,
						'token' => $token,
						'service_allowed' => $provider_data
					];
					// return $this->respondCreated($response);
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Incorrect Details')
					];
					// return $this->respondCreated($response);
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User not found or Inactive')
				];
				// return $this->respondCreated($response);
			}
		} elseif ($user_role == "ota") {
			$userdata = $OtaMoodel->where("email", $this->request->getPost("email"))->where('status','active')->first();
			if (!empty($userdata)) {
				if (password_verify($this->request->getPost("password"), $userdata['password'])) {
					$key = $this->getKey();
					$payload = array(
						"role" => 'Provider',
						"id" => $userdata['id'],
						"date" => date('Y-m-d'),
					);
					// $token = JWT::encode($payload, $key);

					$token = $this->service->getSignedAccessTokenForUser('ota', $userdata['id']);

					$OtaMoodel->update($userdata['id'], ['token' => $token]);
					$db = \Config\Database::connect();
					$query = $db->table('tbl_service_commision_mapping');
					$query->select('service_id, service_type, commision_in_percent');
					$query->where('user_id', $userdata['id']);
					$query->where('user_role', 'provider');
					$otadata = $query->get()->getResultArray();

					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.OTA Logged In Successfully'),
						'user_id' => $userdata['id'],
						'user_role' => $user_role,
						'token' => $token,
						'service_allowed' => $otadata

					];
					// return $this->respondCreated($response);
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Incorrect Details')
					];
					// return $this->respondCreated($response);
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User not found or Inactive')
				];
				// return $this->respondCreated($response);
			}
		} elseif ($user_role == "user") {
			$userdata = $UserModels->where("email", $this->request->getPost("email"))->where('status','active')->first();
			if (!empty($userdata)) {
				if (password_verify($this->request->getPost("password"), $userdata['password'])) {
					$key = $this->getKey();
					$payload = array(
						"role" => 'Provider',
						"id" => $userdata['id'],
						"date" => date('Y-m-d'),
					);
					// $token = JWT::encode($payload, $key);

					$token = $this->service->getSignedAccessTokenForUser('user', $userdata['id']);

					$UserModels->update($userdata['id'], ['token' => $token]);

					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.User logged In Successfully'),
						'user_id' => $userdata['id'],
						'user_role' => $user_role,
						'token' => $token
					];
					// return $this->respondCreated($response);
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Incorrect Details')
					];
					// return $this->respondCreated($response);
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User not found or Inactive')
				];
				// return $this->respondCreated($response);
			}
		} elseif ($user_role == "guide") {
			$userdata = $GuideModel->where("email", $this->request->getPost("email"))->where('status','active')->first();
			if (!empty($userdata)) {
				if (password_verify($this->request->getPost("password"), $userdata['password'])) {
					$key = $this->getKey();
					$payload = array(
						"role" => 'guide',
						"id" => $userdata['id'],
						"date" => date('Y-m-d'),
					);
					// $token = JWT::encode($payload, $key);

					$token = $this->service->getSignedAccessTokenForUser('user', $userdata['id']);

					$GuideModel->update($userdata['id'], ['token' => $token]);

					$guidedoc = $GuideDocModel->select('id,guide_doc')->where("guide_id", $userdata['id'])->findAll();

					$response = [
						'status' => 'success',
						'status_code' => 200,
						'messages' => lang('Language.Guide logged In Successfully'),
						'user_id' => $userdata['id'],
						'user_role' => $user_role,
						'token' => $token,
						'data' => $userdata,
						'guide_doc' => $guidedoc
					];
					// return $this->respondCreated($response);
				} else {
					$response = [
						'status' => 'failed',
						'status_code' => 500,
						'messages' => lang('Language.Incorrect Details')
					];
					// return $this->respondCreated($response);
				}
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.User not found or Inactive')
				];
				// return $this->respondCreated($response);
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

	// all logout  provider  ota users
	public function allLogOut()
	{
		$ProviderModel = new ProviderModel();
		$UserModels = new UserModels();
		$OtaMoodel = new OtaMoodel();
		$GuideModel = new GuideModel();

		$key = $this->getKey();
		$token = $this->request->getPost("authorization");
		$userid = $this->request->getPost("logged_user_id");
		$user_role = $this->request->getPost("logged_user_role");
		if ($user_role == "provider") {
			if (!$this->service->getAccessForSignedUser($token, $user_role)) {
				echo json_encode(['status' => 401, 'message' => 'Access denied']);
				die();
			}
			$info = $ProviderModel->where("id", $userid)->first();
			if (!empty($info)) {
				$res = $ProviderModel->update($info['id'], ['token' => '']);
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Logged Out Successfully')
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Failed to logout')
				];
			}
		} elseif ($user_role == "ota") {
			if (!$this->service->getAccessForSignedUser($token, $user_role)) {
				echo json_encode(['status' => 401, 'message' => 'Access denied']);
				die();
			}
			$info = $OtaMoodel->where("id", $userid)->first();
			if (!empty($info)) {
				$res = $OtaMoodel->update($info['id'], ['token' => '']);
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Logged Out Successfully')
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Failed to logout')
				];
			}
		} elseif ($user_role == "user") {
			if (!$this->service->getAccessForSignedUser($token, $user_role)) {
				echo json_encode(['status' => 401, 'message' => 'Access denied']);
				die();
			}
			$info = $UserModels->where("id", $userid)->first();
			if (!empty($info)) {
				$res = $UserModels->update($info['id'], ['token' => '']);
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Logged Out Successfully')
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Failed to logout')
				];
			}
		} elseif ($user_role == "guide") {
			if (!$this->service->getAccessForSignedUser($token, $user_role)) {
				echo json_encode(['status' => 401, 'message' => 'Access denied']);
				die();
			}
			$info = $GuideModel->where("id", $userid)->first();
			if (!empty($info)) {
				$res = $GuideModel->update($info['id'], ['token' => '']);
				$response = [
					'status' => 'success',
					'status_code' => 200,
					'messages' => lang('Language.Logged Out Successfully')
				];
			} else {
				$response = [
					'status' => 'failed',
					'status_code' => 500,
					'messages' => lang('Language.Failed to logout')
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

	// Change Password  for all
	public function passwordChange()
	{
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$OtaMoodel = new OtaMoodel();
		$UserModels = new UserModels();
		$oldPassword = $this->request->getPost("old_password");
		$newPassword = $this->request->getPost("new_password");
		$confirmPass = $this->request->getPost("confirm_password");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$user_id = $this->request->getPost("logged_user_id");
		$token = $this->request->getPost("authorization");



		if ($logged_user_role == "admin") {
			$userdata = $AdminModel->where("token", $token)->where("id", $user_id)->first();
		} elseif ($logged_user_role == "provider") {
			$userdata = $ProviderModel->where("token", $token)->where("id", $user_id)->first();
		} elseif ($logged_user_role == "ota") {
			$userdata = $OtaMoodel->where("token", $token)->where("id", $user_id)->first();
		} elseif ($logged_user_role == "user") {
			$userdata = $UserModels->where("token", $token)->where("id", $user_id)->first();
		}
		// echo json_encode($userdata);die();
		if (!password_verify($oldPassword, $userdata['password'])) {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang('Language.Current password does not match'),
			];
		} else if ($newPassword != $confirmPass) {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang('Language.Confirm password not match'),
			];
		} else {
			if ($logged_user_role == "admin") {
				$res = $AdminModel->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
			} elseif ($logged_user_role == "provider") {
				$res = $ProviderModel->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
			} elseif ($logged_user_role == "ota") {
				$res = $OtaMoodel->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
			} elseif ($logged_user_role == "user") {
				$res = $UserModels->update($userdata['id'], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
			}
			if ($res) {
				$response = [
					'status' => "success",
					'status_code' => 200,
					'messages' => lang("Language.Password changed successfully")
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
}

/* End of file Auth.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Auth.php */