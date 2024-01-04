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
use App\Models\CheckoutModel;
use App\Models\ServiceCommisionModel;

use App\Libraries\MailSender;

use Slim\Http\Request;
use Slim\Http\Response;
// use Stripe\Stripe;
use Stripe;

require 'vendor/autoload.php';

use App\Models\UserModels;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;

use Exception;
use mysqli;

use Config\Services;

// headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Payment extends ResourceController
{

	private $user_id = null;
	private $user_role = null;
	private $token = null;

	public function __construct()
	{
		$this->service  = new Services();
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

		$str = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
		if ($str != 'accessDefine') {
			checkEmptyPost($_POST);
		}

		$db = \Config\Database::connect();
		// Check Authentication
		$this->token = $token = $_POST['authorization'];
		$this->user_id = $user_id = $_POST['logged_user_id'];
		$this->user_role = $user_role = $_POST['logged_user_role'];

		// if (!$this->service->getAccessForSignedUser($token, $user_role)) {
		// 	echo json_encode(['status' => 'failed', 'messages' => 'Access denied']);
		// 	die();
		// }

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


	// Check Authintication
	public function checkAuthentication($token = '', $userid = '', $role = '')
	{
		//  echo json_encode("hi");die();
		$ProviderModel = new ProviderModel();
		$AdminModel = new AdminModel();
		$ProviderModel = new ProviderModel();
		$UserModels = new UserModels();
		$OtaMoodel = new OtaMoodel();

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

	// public function 
	public function paymentStripeCheckout()
	{

		$CheckoutModel = new CheckoutModel();
		$price = $this->request->getPost("price");
		$package_name = $this->request->getPost("package_name");
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$service_type = $this->request->getPost("service_type");
		$service_id = $this->request->getPost("service_id");
		$ota_id = $this->request->getPost("ota_id");
		$guest_fullname = $this->request->getPost("guest_fullname");
		$guest_contact_no = $this->request->getPost("guest_contact_no");
		$guest_email = $this->request->getPost("guest_email");


		$stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);

		$session = \Stripe\Checkout\Session::create([
			'line_items' => [[
				'price_data' => [
					'currency' => 'SAR',
					'product_data' => [
						'name' => $package_name,
					],
					'unit_amount' => $price * 100,
				],
				'quantity' => 1,
			]],

			'mode' => 'payment',
			'success_url' => 'https://example.com/success',
			'cancel_url' => 'https://example.com/cancel',
		]);
		// echo json_encode($session);die();
		$data = [
			'session_id' => $session->id,
			'object' => $session->object,
			'amount_total' => $session->amount_total,
			'currency' => $session->currency,
			// 'customer_email'=>$session->customer_email,
			'payment_intent' => $session->payment_intent,
			'payment_status' => $session->payment_status,
			'url' => $session->url,
			// 'customer_details'=>$session->customer_details,
			'user_id' => $logged_user_id,
			'user_role' => $logged_user_role,
			'ota_id' => $ota_id,
			'service_id' => $service_id,
			'service_type' => $service_type,
			'status' => 'active',
			'guest_fullname' => $guest_fullname,
			'guest_contact_no' => $guest_contact_no,
			'guest_email' => $guest_email
		];

		if ($CheckoutModel->insert($data)) {

			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang("Language.Session CheckOut Given"),
				'info' => $session
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.Payment Failed")
			];
		}
		return $this->respondCreated($response);
	}


	//  Success Payment aafer chekout
	public function successPayment()
	{
		$ServiceCommisionModel = new ServiceCommisionModel();
		$CheckoutModel = new CheckoutModel();
		$UserModels = new UserModels();
		$PackageModels = new PackageModels();
		$BookingModel = new BookingModel();
		$VehicleModels = new VehicleModels();
		$OtaMoodel = new OtaMoodel();
		$ProviderModel = new ProviderModel();
		$OtaProviderAccountModel = new OtaProviderAccountModel();
		$AccountModel = new AccountModel();
		$Admin_transaction_Model = new Admin_transaction_Model();
		$User_transaction_Model = new User_transaction_Model();

		$session_id = $this->request->getPost("session_id");
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$service_id = $this->request->getPost("service_id");
		$ota_id = $this->request->getPost("ota_id");
		$service_type = "package";
		$active = "active";
		$pax_id = $this->request->getPost('pax_id');
		$date = $this->request->getPost('date');
		$user_pax = $this->request->getPost('user_pax');
		$status = $this->request->getPost('status');
		$guest_email = $this->request->getPost('guest_email');
		$guest_contact_no = $this->request->getPost('guest_contact_no');
		$guest_fullname = $this->request->getPost('guest_fullname');

		// $stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);
		// check session id in session data 
		$check_box_data = $CheckoutModel->where('session_id', $session_id)->first();
		if (empty($check_box_data)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Checkout Not Found')]);
			die();
		}

		// check ota
		$otadata = $OtaMoodel->where("id", $ota_id)->where('status', $active)->first();
		if (empty($otadata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
			die();
		}

		// package data
		$packagedata = $PackageModels->where("id", $service_id)->where("status_by_admin", $active)->where("status", $active)->first();
		if (empty($packagedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		// echo json_encode($packagedata);
		// checckout id
		$checkoutid = $check_box_data['id'];

		// mandatory booking detail
		$provider_id = $packagedata['provider_id'];
		// echo json_encode($provider_id);die();

		$car_data = $VehicleModels->where("id", $pax_id)->where("package_id", $service_id)->where("status", $active)->first();

		$rate = $car_data['rate'];
		$no_of_pox = $car_data['no_of_pox_id'];
		$cars = $car_data['vehicle_id'];

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


		if ($status == 'success') {
			$stripe = new \Stripe\StripeClient(
				STRIPE_SECRET
			);
			$stripe_session_data = $stripe->checkout->sessions->retrieve(
				$session_id,
				[]
			);
			// echo json_encode($stripe_session_data);die();

			$inprocessbooking = [
				'service_type' => $service_type,
				'service_id' => $service_id,
				'user_id' => $logged_user_id,
				'user_role' => $logged_user_role,
				'from_date' => $date,
				'no_of_pox' => $no_of_pox,
				'user_pax' => $user_pax,
				'cars' => $cars,
				'rate' => $rate,
				'provider_id' => $provider_id,
				'ota_id' => $ota_id,
				"booked_time" => date("h:i:sa"),
				"booked_date" => date("Y-m-d"),
				'booking_status_user' => 'in-progress',
				'booking_status_stripe' => 'open',
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
				'guest_fullname' => $guest_fullname,
				'guest_contact_no' => $guest_contact_no,
				'guest_email' => $guest_email,
			];

			if ($BookingModel->insert($inprocessbooking)) {

				$lastbooking_id = $BookingModel->insertID;

				// $confirmPayment = [
				// 	'customer_stripe_email' => $stripe_session_data->customer_details->email,
				// 	'customer_stripe_id' => $stripe_session_data->customer,
				// 	'customer_stripe_name' => $stripe_session_data->customer_details->name,
				// 	'payment_status' => $stripe_session_data->payment_status,
				// 	'url' => '',
				// 	'stripe_status' => $stripe_session_data->status,
				// 	'customer_details' => $stripe_session_data->customer_details
				// ];
				// $updatecheckout = $CheckoutModel->update($checkoutid, $confirmPayment);
				
				// $confirm_payment_status = $confirmPayment['payment_status'];
				if ($stripe_session_data['payment_status'] == 'paid') {    
				    
					$confirm_booking = [
						'booking_status_user' => 'confirm',
						'booking_status_stripe' => $stripe_session_data->status,
						'payment_status' => 'completed'
					];
					$update_Booking = $BookingModel->update($lastbooking_id, $confirm_booking);

					// SEND EAMIL TO PROVIDER on PAckage Booking
					$Providerdata = $ProviderModel->where("id", $provider_id)->first();
					$providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];

					$data = array('user_role' => 'provider','user_name' => $guest_fullname, 'provider_name' => $providerFullname, 'package_name'=>$packagedata['package_title']);
					$msg_template = view('emmail_templates/package_booking.php', $data);
					$subject      = 'Package Booked';
					$to_email     =  $Providerdata['email']; // provider email
					$filename = "";
					$send     = sendEmail($to_email, $subject, $msg_template,$filename);
					// SEND EAMIL TO USER on PAckage Booking
					$data = array('user_role' => 'user','user_name' => $guest_fullname, 'provider_name' => $providerFullname, 'package_name'=>$packagedata['package_title']);
					$msg_template = view('emmail_templates/package_booking.php', $data);
					$subject      = 'Package Booked';
					$to_email     =  $guest_email; // user email
					$filename = "";
					$send     = sendEmail($to_email, $subject, $msg_template,$filename);					// EnD

					// for  provider 
					$providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $provider_id)->first();
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
						'service_id' => $service_id,
						'transaction_reason' => "Credit Amount of " . $service_type . " Of User",
						'currency_code' => 'SAR',
						'account_id' => 1,
						'old_balance' => $old_balance,
						'transaction_amount' => $rate,
						'current_balance' => $old_balance + $rate,
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
						'amount' => $old_balance + $rate
					];
					$AccountModel->update('1', $admin_account);
					$user_transaction = [
						'customer_id' => $logged_user_id,
						'user_id' => '1',
						'user_type' => 'admin',
						'transaction_type' => 'Dr',
						'transaction_reason' => 'Package Amount to Admin',
						'currency_code' => 'SAR',
						'transaction_amount' => $rate,
						'transaction_id' => $transaction_id,
						'transaction_status' => 'success',
						'transaction_date' => date("Y-m-d"),
						'service_type' => $service_type,
						'service_id' => $service_id,
						'payment_method' => 'STRIPE'
					];
					$User_transaction_Model->insert($user_transaction);
					$response = [
						'status' => "success",
						'status_code' => 200,
						'messages' => lang('Language.Payment Accept')
					];
				} else {
					$response = [
						'status' => "failed",
						'status_code' => 404,
						'messages' => lang('Language.Transaction Failed')
					];
				}
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 404,
					'messages' => lang('Language.Booking Failed')
				];
			}
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.Transaction  Failed')
			];
		}
		return $this->respondCreated($response);
	}

	//  booking package for user with stripe
	public function packageBookingUser()
	{
		$UserModels = new UserModels();
		$PackageModels = new PackageModels();
		$BookingModel = new BookingModel();
		$VehicleModels = new VehicleModels();
		$OtaMoodel = new OtaMoodel();
		$CheckoutModel = new CheckoutModel();


		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");

		$service_type = "package";

		$service_id = $this->request->getPost("service_id");
		$ota_id = $this->request->getPost("ota_id");
		$pax_id = $this->request->getPost('pax_id');
		$date = $this->request->getPost('date');
		$user_pax = $this->request->getPost('user_pax');
		$active = "active";

		// check ota
		$otadata = $OtaMoodel->where("id", $ota_id)->where('status', $active)->first();
		if (empty($otadata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
			die();
		}

		$packagedata = $PackageModels->where("id", $service_id)->where("status_by_admin", $active)->where("status", $active)->first();
		if (empty($packagedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		$userdata = $UserModels->where("id", $logged_user_id)->where("status", $active)->first();
		if (empty($userdata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.User Not Found')]);
			die();
		}


		$provider_id = $packagedata['provider_id'];
		$package_title = $packagedata['package_title'];

		// echo json_encode($packagedata);die();
		$car_data = $VehicleModels->where("id", $pax_id)->where("package_id", $service_id)->where("status", $active)->first();
		$rate = $car_data['rate'];
		// echo json_encode($rate);die();
		$no_of_pox = $car_data['no_of_pox_id'];
		$cars = $car_data['vehicle_id'];

		$stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);

		$session = \Stripe\Checkout\Session::create([
			'line_items' => [[
				'price_data' => [
					'currency' => 'SAR',
					'product_data' => [
						'name' => $package_title,
					],
					'unit_amount' => $rate * 100,
				],
				'quantity' => 1,
			]],

			'mode' => 'payment',
			'success_url' => 'https://example.com/success',
			'cancel_url' => 'https://example.com/cancel',
		]);

		$data = [
			'session_id' => $session->id,
			'object' => $session->object,
			'amount_total' => $session->amount_total / 100,
			'currency' => $session->currency,
			'payment_intent' => $session->payment_intent,
			'payment_status' => $session->payment_status,
			'stripe_status' => $session->status,
			'url' => $session->url,
			'user_id' => $logged_user_id,
			'user_role' => $logged_user_role,
			'ota_id' => $ota_id,
			'service_id' => $service_id,
			'service_type' => $service_type,
			'status' => 'active'
		];
		// echo json_encode($data);die();
		if ($CheckoutModel->insert($data)) {
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang("Language.Session CheckOut Given"),
				'info' => $session
			];
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 500,
				'messages' => lang("Language.Session CheckOut Failed")
			];
		}
		return $this->respondCreated($response);
	}

	//failed payment after check out 
	public function failedPayment()
	{
		$ServiceCommisionModel = new ServiceCommisionModel();
		$CheckoutModel = new CheckoutModel();
		$UserModels = new UserModels();
		$PackageModels = new PackageModels();
		$BookingModel = new BookingModel();
		$VehicleModels = new VehicleModels();
		$OtaMoodel = new OtaMoodel();
		$ProviderModel = new ProviderModel();

		$session_id = $this->request->getPost("session_id");
		$logged_user_id = $this->request->getPost("logged_user_id");
		$logged_user_role = $this->request->getPost("logged_user_role");
		$service_id = $this->request->getPost("service_id");
		$ota_id = $this->request->getPost("ota_id");
		$service_type = "package";
		$active = "active";
		$pax_id = $this->request->getPost('pax_id');
		$date = $this->request->getPost('date');
		$user_pax = $this->request->getPost('user_pax');
		$status = $this->request->getPost('status');
		$guest_email = $this->request->getPost('guest_email');
		$guest_contact_no = $this->request->getPost('guest_contact_no');
		$guest_fullname = $this->request->getPost('guest_fullname');



		// $stripe =  Stripe\Stripe::setApiKey(STRIPE_SECRET);


		// check session id in session data 
		$check_box_data = $CheckoutModel->where('session_id', $session_id)->first();
		if (empty($check_box_data)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Checkout Not Found')]);
			die();
		}

		// check ota
		$otadata = $OtaMoodel->where("id", $ota_id)->where('status', $active)->first();
		if (empty($otadata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
			die();
		}

		// package data
		$packagedata = $PackageModels->where("id", $service_id)->where("status_by_admin", $active)->where("status", $active)->first();
		if (empty($packagedata)) {
			echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
			die();
		}

		//checckout id
		$checkoutid = $check_box_data['id'];

		// mandatory booking detail
		$provider_id = $packagedata['provider_id'];
		$car_data = $VehicleModels->where("id", $pax_id)->where("package_id", $service_id)->where("status", $active)->first();
		$rate = $car_data['rate'];
		$no_of_pox = $car_data['no_of_pox_id'];
		$cars = $car_data['vehicle_id'];

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

		if ($status == 'failed') {
			// first expire session 
			$stripe = new \Stripe\StripeClient(
				STRIPE_SECRET
			);
			$stripe_session_data = $stripe->checkout->sessions->expire(
				$session_id,
				[]
			);

			

			$inprocessbooking = [
				'service_type' => $service_type,
				'service_id' => $service_id,
				'user_id' => $logged_user_id,
				'user_role' => $logged_user_role,
				'from_date' => $date,
				'no_of_pox' => $no_of_pox,
				'user_pax' => $user_pax,
				'cars' => $cars,
				'rate' => $rate,
				'provider_id' => $provider_id,
				'ota_id' => $ota_id,
				"booked_time" => date("h:i:sa"),
				"booked_date" => date("Y-m-d"),
				'booking_status_user' => 'in-progress',
				'booking_status_stripe' => 'open',
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
				'guest_fullname' => $guest_fullname,
				'guest_contact_no' => $guest_contact_no,
				'guest_email' => $guest_email
			];


			if ($BookingModel->insert($inprocessbooking)) {
				$lastbooking_id = $BookingModel->insertID;

				$confirmPayment = [
					'customer_stripe_email' => $stripe_session_data->customer_details->email,
					'customer_stripe_id' => $stripe_session_data->customer,
					'customer_stripe_name' => $stripe_session_data->customer_details->name,
					'payment_status' => $stripe_session_data->payment_status,
					'stripe_status' => $stripe_session_data->status,
					'customer_details' => $stripe_session_data->customer_details
				];


				$updatecheckout = $CheckoutModel->update($checkoutid, $confirmPayment);
				$confirm_payment_status = $confirmPayment['payment_status'];

				if ($confirm_payment_status == 'unpaid') {
					$confirm_booking = [
						'booking_status_user' => 'failed',
						'booking_status_stripe' => 'failed',
						'payment_status' => 'pending'
					];

					$update_Booking = $BookingModel->update($lastbooking_id, $confirm_booking);


					$response = [
						'status' => "success",
						'status_code' => 200,
						'messages' => lang('Language.Payment Failed')
					];
				} else {
					$response = [
						'status' => "failed",
						'status_code' => 404,
						'messages' => lang('Language.Transaction Failed')
					];
				}
			} else {
				$response = [
					'status' => "failed",
					'status_code' => 404,
					'messages' => lang('Language.Booking Failed')
				];
			}
		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.Transaction  Failed')
			];
		}
		return $this->respondCreated($response);
	}

	// COD PAYMENT - 14 OCT 2022 - RIZ
	public function packageCodBooking()
	{
		// echo "YES"; exit;
		$ServiceCommisionModel = new ServiceCommisionModel();
        $CheckoutModel = new CheckoutModel();
        $UserModels = new UserModels();
        $PackageModels = new PackageModels();
        $BookingModel = new BookingModel();
        $VehicleModels = new VehicleModels();
        $OtaMoodel = new OtaMoodel();
        $ProviderModel = new ProviderModel();
        $OtaProviderAccountModel = new OtaProviderAccountModel();
        $AccountModel = new AccountModel();
        $Admin_transaction_Model = new Admin_transaction_Model();
        $User_transaction_Model = new User_transaction_Model();

		$logged_user_id = $this->request->getPost("logged_user_id");
        $logged_user_role = $this->request->getPost("logged_user_role");
        $service_id = $this->request->getPost("service_id");
        $ota_id = $this->request->getPost("ota_id");
        $service_type = "package";
        $active = "active";
        $pax_id = $this->request->getPost('pax_id');
        $date = $this->request->getPost('date');
        $user_pax = $this->request->getPost('user_pax');
        $status = $this->request->getPost('status');
        $guest_email = $this->request->getPost('guest_email');
        $guest_contact_no = $this->request->getPost('guest_contact_no');
        $guest_fullname = $this->request->getPost('guest_fullname');

		// check ota
        $otadata = $OtaMoodel->where("id", $ota_id)->where('status', $active)->first();
        if (empty($otadata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.OTA Not Exists')]);
            die();
        }

		// package data
        $packagedata = $PackageModels->where("id", $service_id)->where("status_by_admin", $active)->where("status", $active)->first();
        if (empty($packagedata)) {
            echo json_encode(['status' => 'failed', 'messages' => lang('Language.Package Not Found')]);
            die();
        }

		$provider_id = $packagedata['provider_id'];

		$car_data = $VehicleModels->where("id", $pax_id)->where("package_id", $service_id)->where("status", $active)->first();
        $rate = (!empty($car_data['rate']))?$car_data['rate']:'0';
        $no_of_pox = (!empty($car_data['no_of_pox_id']))?$car_data['no_of_pox_id']:'0';
        $cars = (!empty($car_data['vehicle_id']))?$car_data['vehicle_id']:'0';

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

		$inprocessbooking = [
			'service_type' => $service_type,
			'service_id' => $service_id,
			'user_id' => $logged_user_id,
			'user_role' => $logged_user_role,
			'from_date' => $date,
			'no_of_pox' => $no_of_pox,
			'user_pax' => $user_pax,
			'cars' => $cars,
			'rate' => $rate,
			'provider_id' => $provider_id,
			'ota_id' => $ota_id,
			"booked_time" => date("h:i:sa"),
			"booked_date" => date("Y-m-d"),
			'booking_status_user' => 'confirm',
			'booking_status_stripe' => 'completed',
			'payment_status' => 'completed',
			'ota_commision' => $ota_commision,
			'provider_commision' => $admin_commision_per,
			'total_admin_comm_amount' => $admin_amount,
			'remaining_admin_comm_amount' => $remaining_admin_comm_amount,
			'ota_commision_amount' => $ota_ammount,
			'provider_amount' => $provider_amount,
			'ota_payment_status' => 'pending',
			'provider_payment_status' => 'pending',
			'session_id' => "",
			'checkout_id' => "COD",
			'guest_fullname' => $guest_fullname,
			'guest_contact_no' => $guest_contact_no,
			'guest_email' => $guest_email ? $guest_email : "",
		];

		if ($BookingModel->insert($inprocessbooking)) { 

			$lastbooking_id = $BookingModel->insertID;

			// SEND EAMIL TO PROVIDER on PAckage Booking
			$Providerdata = $ProviderModel->where("id", $provider_id)->first();
			$providerFullname = $Providerdata['firstname'].' '.$Providerdata['lastname'];

			$data = array('user_role' => 'provider','user_name' => $guest_fullname, 'provider_name' => $providerFullname, 'package_name'=>$packagedata['package_title']);
			$msg_template = view('emmail_templates/package_booking.php', $data);
			$subject      = 'Package Booked';
			$to_email     =  $Providerdata['email']; // provider email
            $filename = "";
            $send     = sendEmail($to_email, $subject, $msg_template,$filename);
			// SEND EAMIL TO USER on PAckage Booking
			$data = array('user_role' => 'user','user_name' => $guest_fullname, 'provider_name' => $providerFullname, 'package_name'=>$packagedata['package_title']);
			$msg_template = view('emmail_templates/package_booking.php', $data);
			$subject      = 'Package Booked';
			if ($guest_email) {
				$to_email     =  $guest_email; // user email
				$filename = "";
				$send     = sendEmail($to_email, $subject, $msg_template,$filename);
			}			// EnD

			// for  provider 
			$providerAccount = $OtaProviderAccountModel->where('user_role', 'provider')->where('user_id', $provider_id)->first();
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
				'service_id' => $service_id,
				'transaction_reason' => "Credit Amount of " . $service_type . " Of User",
				'currency_code' => 'SAR',
				'account_id' => 1,
				'old_balance' => $old_balance,
				'transaction_amount' => $rate,
				'current_balance' => $old_balance + $rate,
				'transaction_id' => generateRandomString('TRANSACTION'),
				'transaction_status' => 'success',
				'transaction_date' => date("Y-m-d"),
				'payment_method' => 'STRIPE',
				'booking_id'  => $lastbooking_id,
				'payment_session_id' => ""
			];
			$transaction_id = $admin_transaction['transaction_id'];
			$Admin_transaction_Model->insert($admin_transaction);

			// user transaction
			$admin_account = [
				'amount' => $old_balance + $rate
			];
			$AccountModel->update('1', $admin_account);
			$user_transaction = [
				'customer_id' => $logged_user_id,
				'user_id' => '1',
				'user_type' => 'admin',
				'transaction_type' => 'Dr',
				'transaction_reason' => 'Package Amount to Admin',
				'currency_code' => 'SAR',
				'transaction_amount' => $rate,
				'transaction_id' => $transaction_id,
				'transaction_status' => 'success',
				'transaction_date' => date("Y-m-d"),
				'service_type' => $service_type,
				'service_id' => $service_id,
				'payment_method' => 'COD'
			];

			$User_transaction_Model->insert($user_transaction);
			$response = [
				'status' => "success",
				'status_code' => 200,
				'messages' => lang('Language.Payment Accept')
			];

		} else {
			$response = [
				'status' => "failed",
				'status_code' => 404,
				'messages' => lang('Language.Booking Failed')
			];
		}
		return $this->respondCreated($response);
	}

} // class end

/* End of file Payment.php */
/* Location: .//C/xampp/htdocs/Umrah/app/Controllers/Payment.php */
