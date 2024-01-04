<?php
use Config\Services;

if(!function_exists('checkEmptyPost')) {
	function checkEmptyPost($data=[])
	{
		foreach($data as $key => $value) 
	  { if(trim($value) == ""){ $errors[] = ucwords(str_replace('_', ' ', $key));}}
		if(!empty($errors)) { echo json_encode(array('response'=>'failed', 'message'=>'Empty field(s) - '.implode(', ', $errors), 'response_code'=>100));die(); }
	}
}

if(!function_exists('getUserRole'))
{
	function getUserRole($id=null)
	{
		if($id==1){	return "admin";} 
		else if($id==2){ return "provider"; }
		else if($id==3){ return "ota"; }
		else if($id==4){ return "customer"; }
	}
}

if(!function_exists('generateRandomString')) {
	function generateRandomString($type='')
	{	
		$db = Config\Database::connect();
		$db->transStart();
		$code = model('App\Models\CodeModel');

    $res = $code->where('type',$type)->first();
    $code->update($res['id'], ['next_value' => $res['next_value']+1]);

    $res = $code->where('type',$type)->first();
		$key = trim($res['prefix']);
    $key .= str_pad($res['next_value'],$res['code_min_length'],'0',STR_PAD_LEFT);
		$db->transComplete();
    return $key;
	}
}

if(!function_exists('check_staff_members_privilege'))
{
	function check_staff_members_privilege($staff_member_id=null, $user_role=null, $function_name=null, $function_role=null)
	{
		$db = Config\Database::connect();
		$staff = model('App\Models\PrivilegeModel');
		$privilege = $staff->where('staff_member_id',$staff_member_id)->first();
	 	if($user_role==1 && is_array($privilege))
	  {
      $function_array = explode(',', $privilege[$function_name]);
      if(!in_array($function_role, $function_array))
      {
        if(!in_array(7, $function_array))
        {
        	echo json_encode(array('response'=>"failed", 'message'=>"Access denied you don't have sufficient privilege", 'response_code'=>404));
        }
      }
    }
	}
}

if (!function_exists('sendEmail')) {
    function sendEmail($to_email, $subject, $message,$filename)
    {
        $email = \Config\Services::email();
        $email->setFrom('noori.developer@gmail.com','Umrah Plus', $subject);
        $email->setTo($to_email);
        $email->setSubject($subject);
        $email->setMessage($message); //your message here

        // $email->setCC('another@emailHere');//CC
        // $email->setBCC('thirdEmail@emialHere');// and BCC
        // $filename = '/img/yourPhoto.jpg'; //you can use the App patch 
        if(!empty($filename)){

            $email->attach($filename);
        }

        if ($email->send()) {
            $email->printDebugger(['headers']);
            return true;
        } else {
            return false;
        }
    }
}