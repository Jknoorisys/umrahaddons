<?php
use Config\Services;

if(!function_exists('sendFCMMessage________'))
{
  function sendFCMMessage________($data,$target)
  { 
    $ci =& get_instance();
    $val=$ci->db->where('key','firebase_server_key')->where('status',1)->get('system_settings')->row();
    $firebase_api = trim($val->value);
    $value[]=$target;
    $fields = array(
        'registration_ids' => $value,
        'data' => $data,
    );

    // Set POST variables
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
      'Authorization: key=' . $firebase_api,
      'Content-Type: application/json'
  	);

    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarily
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);  
    if($result === FALSE)
    { 
      die('Curl failed: ' . curl_error($ch));
  	}
    // Close connection
  	curl_close($ch);
	}
}

// In app Notification Insert
if(!function_exists('pushNotifications'))
{
	function pushNotifications($sender_id='',$sender_role='',$receiver_id='',$receiver_role='',$message='')
	{
		$db = Config\Database::connect();
		$db->transStart();
		$model = model('App\Models\NotificationModel');
		$data = [
			"message" => $message,
			"sender_id" => $sender_id,
			"receiver_id" => $receiver_id,
			"sender_user_role" => $sender_role,
			"receiver_user_role" => $receiver_role,
		];
		$model->insert($data);
		$db->transComplete();
	}
}

// FCM NOTIFICATION - RIZ - 08 SEP 2022
if(!function_exists('sendFCMMessage')){
  function sendFCMMessage($data,$target){ 
    $firebase_api = "AAAAfT1XFtU:APA91bHtcD5N3FKcTY0q_U55UWMyAUoDvumK8wZNJdVe_O9gSUBFmqTDIIyHAwK7yB2pPcgHyni0Pyv7Z3wvffcsQqIpnm4Ux4RhEwH6lzhkn7QAbLFgZFA61_fRbc43ae9Qvt7ayJxF";

    $value=$target;
    $fields = array(
        'registration_ids' => $value,
        'data' => $data,
        "priority" => "high",
        'notification' => array(
        "title" => $data['title'],
        "body" => $data['message'],
      )
    );
    
    // Set POST variables
    $url = 'https://fcm.googleapis.com/fcm/send';
    $headers = array(
      'Authorization: key=' . $firebase_api,
      'Content-Type: application/json'
    );
    
    // Open connection
    $ch = curl_init();
    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Disabling SSL Certificate support temporarily
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    // Execute post
    $result = curl_exec($ch);  
    // if($result === FALSE) {   die('Curl failed: ' . curl_error($ch)); }
    // Close connection
    curl_close($ch);

    return $result;
  }
}

/// Calling method
// Notifications
    // $notification=array(
    //   'title' => $title ,
    //   'message' => $message,
    //   'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // DO NOT CHANGE THE VALUE
    //   'date' => date('Y-m-d H:i'),
    //   'id' => $id,
    // );
    // 
    
    // sendFCMMessage($notification, $fmc_ids);
    // $fmc_ids = array('fsdjlaksjfladkfjdsalfjldasfaj;lfjaflkasf', 'fjlksdajflsdajfadlskfjslkfjsafjasf', 'sjfslda;fjsdlakfjsdajfsjflskafj');
// sendFCMMessage($notification, $fmc_ids);

?>