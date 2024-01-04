<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;



/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    public function getSignedAccessTokenForUser( string $email, $Uid )
    {
        if ( !empty( $email ) ) {

            $expire_at = date('d-m-Y H:i:s', strtotime(getenv('JWT_TOKEN_TIME')));  
			$jwtBody    =   [
				'iat'   => strtotime(date('d-m-Y H:i')),
				'iss'   => 'Welzo',
				'nbf'   => strtotime(date('d-m-Y H:i')),
				'exp'   => strtotime($expire_at),
				'sub'   => 'Welzo authentication',
				"email" => $email,
				"uid"   => $this->encryption( $Uid, 1),
			];

            return JWT::encode($jwtBody, getenv('JWT_SECRET'), getenv('JWT_ALGO'));
        }
        return false;
    }

    public function getAccessForSignedUser(string $token)
    {
        if (!empty($token)) {
            return JWT::decode($token, new Key(getenv('JWT_SECRET'), getenv('JWT_ALGO')));
        }
        return false;
    }

    public function success(array $json, int $code = ResponseInterface::HTTP_OK, $response)
    {
        $data = [
            'status'            =>  "success",
            'message'           =>  $json['message'],
            'error'             =>  "",
            'data'              =>  $json['data'],
            'response_datetime' =>  Time::now(getenv('TIMEZONE'))->toDateTimeString()
        ];
        return $this->sendResponse($data, $code, $response);
    }

    public function fail(array $json, int $code = ResponseInterface::HTTP_BAD_REQUEST, $response)
    {
        $data = [
            'status'            =>  "failed",
            'errors'            =>  $json['errors'],
            'message'           =>  $json['message'],
            'response_datetime' =>  Time::now(getenv('TIMEZONE'))->toDateTimeString(),
        ];
        return  $this->sendResponse($data, $code, $response);
    }

    protected function sendResponse(array $body, int $code, $response)
    {
        return $response
            ->setStatusCode($code)
            ->setJSON($body);
    }
    
    public function encryption( string $data, int $action )
    {
        if ( !empty( $data ) && is_numeric( $action ) ) {
            $output = false;
            $KEY = hash('sha256', getenv('ENCRYPTION_KEY'));
            $IV = substr(hash('sha256', getenv('ENCRYPTION_IV')), 0, 16);

            // 1 ---> Encryptinon   0-----> Decryptinon
            if ($action == 1) {
                $output = openssl_encrypt($data, getenv('ENCRYPTION_ALG'), $KEY, 0, $IV);
            } else if ($action == 0) {
                $output = openssl_decrypt($data, getenv('ENCRYPTION_ALG'), $KEY, 0, $IV);
            }
            return $output;
        } return false;
    }


    public function validate_null_string(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value))
                $data[$key] = $this->validate_null_string($value);
            else {
                if (is_null($value) || $value == '' || $value == null || $value == "NULL")
                    $data[$key] = "";
            }
        }
        return $data;
    }
    
    public function curlRequest( array $data  )
    {
        if ( !empty( $data ) && is_array( $data ) ) {
            $header             =   ["Content-Type: application/json", "X-Shopify-Access-Token:".getenv('SHOPIFY_ACCESS_TOKEN')];
            $callUrl            =   TRIM($data['url']);
            $fields             =   ( !empty($data['fields']) )? json_encode( $data['fields'] ) : '' ;
    
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => $callUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_CUSTOMREQUEST => $data['method'],
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_HTTPHEADER => $header
            ));
    
            $response = curl_exec($curl);
            curl_close($curl);
    
            return json_decode($response);

        } return false;
    }

    public function verify_webhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, getenv('SHOPIFY_API_SECRET_KEY'), true));
        return hash_equals($hmac_header, $calculated_hmac);
    }

    public function cors()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
        header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
    }

}
