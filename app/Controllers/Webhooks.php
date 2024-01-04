<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\Admin_transaction_Model;

use Config\Services;
use Exception;

use Stripe;

class Webhooks extends ResourceController
{
    public function index()
    {
        exit('No direct script access allowed.');
    }

    public function paymentSuccess()
    {
        // echo "Payment Success"; exit;

        require 'vendor/autoload.php';
        
        // Set your secret key. Remember to switch to your live secret key in production.
        //https://dashboard.stripe.com/apikeys See your keys here: https://dashboard.stripe.com/apikeys
        \Stripe\Stripe::setApiKey('sk_test_51JuYdBSFsYysxf5iyMXPJNszKEulb2IxYuLZEE6iTlwEPIuJgFJkCu2JNnQh0sFZw10KF74yC5OCgHKx7ediEZcS00KCjNyH3F');
        
        // If you are testing your webhook locally with the Stripe CLI you
        // can find the endpoint's secret by running `stripe listen`
        // Otherwise, find your endpoint's secret in your webhook settings in the Developer Dashboard
        $endpoint_secret = 'whsec_E9VSyUdKDLboexsoaJxwAjfvMsfwEe0r';

        
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }
        
        // echo json_encode($event); exit;

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                
                // echo json_encode($paymentIntent); 
                
                $paymentIntenID = $paymentIntent->id;
                $amount = $paymentIntent->amount / 100;
                $currency = $paymentIntent->currency;
                $name = $paymentIntent->billing_details['name'];
                $email = $paymentIntent->billing_details['email'];
                $payment_intent = $paymentIntent->payment_intent;
                $payment_status = $paymentIntent->status;
                
                $db = db_connect(); 
                $builder = $db->table('webhook_check');
                $data = [
                    'payment_id'   => $paymentIntenID,
                    'payment_intent' => $payment_intent, 
                    'name'         => $name,
                    'email'        => $email,
                    'amount'       => $amount,
                    'payment_status' => $payment_status,
                    'event_name'   => 'charge.succeeded',
                    'created_date' => date('Y-m-d H:i:s'),
                ];
                $builder->insert($data);
                
                $checkoutData = $db->table('tbl_payment_checkout');
                $checkdata = [
                    'customer_stripe_email' => $email,
    				'customer_stripe_id' => $paymentIntent->customer,
    				'customer_stripe_name' => $name,
    				'payment_status' => $payment_status,
    				'url' => '',
    				'stripe_status' => $payment_status,
    				'customer_details' => $paymentIntent->billing_details
                ];
                
                $checkoutData->where('payment_intent', $payment_intent);
                $checkoutData->update($checkdata);
    
                break;

                case 'charge.failed':
                    $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                    
                    // echo json_encode($paymentIntent);
                    
                    $paymentIntenID = $paymentIntent->id;
                    $amount = $paymentIntent->amount / 100;
                    $currency = $paymentIntent->currency;
                    $name = $paymentIntent->billing_details['name'];
                    $email = $paymentIntent->billing_details['email'];
                    $payment_intent = $paymentIntent->payment_intent;
                    $reason = $paymentIntent->failure_message;
                    $payment_status = $paymentIntent->status;
    
                    $db = db_connect(); 
                    $builder = $db->table('webhook_check');
                    $data = [
                        'payment_id'   => $paymentIntenID,
                        'payment_intent' => $payment_intent, 
                        'name'         => $name,
                        'email'        => $email,
                        'amount'       => $amount,
                        'payment_status' => $payment_status,
                        'event_name'   => 'charge.failed',
                        'fail_reason'  => $reason,
                        'created_date' => date('Y-m-d H:i:s'),
                    ];
                    $builder->insert($data);
                    
                    $checkoutData = $db->table('tbl_payment_checkout');
                    $checkdata = [
                        'customer_stripe_email' => $email,
        				'customer_stripe_id' => $paymentIntent->customer,
        				'customer_stripe_name' => $name,
        				'payment_status' => $payment_status,
        				'url' => '',
        				'stripe_status' => $payment_status,
        				'customer_details' => $paymentIntent->billing_details
                    ];
                    
                    $checkoutData->where('payment_intent', $payment_intent);
                    $checkoutData->update($checkdata);
    
                    break;


            case 'payment_method.attached':
                $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
                // handlePaymentMethodAttached($paymentMethod);
                break;
            // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);

    }

    public function createWebhook()
    {
       \Stripe\Stripe::setApiKey('sk_test_51JuYdBSFsYysxf5iyMXPJNszKEulb2IxYuLZEE6iTlwEPIuJgFJkCu2JNnQh0sFZw10KF74yC5OCgHKx7ediEZcS00KCjNyH3F');

        $endpoint = \Stripe\WebhookEndpoint::create([
        'url' => 'https://46f2-223-181-158-102.in.ngrok.io/umrahaddons/webhooks/paymentSuccess',
        'enabled_events' => [
                'payment_intent.succeeded',
            ],
        ]);    

        echo json_encode($endpoint); exit;
    }
}
