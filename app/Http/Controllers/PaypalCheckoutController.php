<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;
use Sample\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaypalCheckoutController extends Controller
{

    private $environment;
    private $client;

    public function __construct()
    {
        $this->environment = new SandboxEnvironment(config('paypal.client_id'), config('paypal.secret'));
        $this->client = new PayPalHttpClient($this->environment);
    }

    public function index(Request $request)
    {
        return view('payment.checkout');
    }

    public function createOrder(Request $request) 
    {
        
        $order = new OrdersCreateRequest();
        $order->prefer('return=representation');

        $order->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "test_ref_id1",
                "amount" => [
                    "value" => "422.00",
                    "currency_code" => "PHP"
                ]
            ]],
            "application_context" => [
                 "cancel_url" => "http://testproject.test/billing/checkout",
                 "return_url" => "http://testproject.test/billing/checkout"
            ] 
        ];

        try {
            $result = $this->client->execute($order);
            return response()->json($result);
        }
        catch(HttpException $ex) {
            return response()->json($ex->getMessage());
        }
    }

    public function captureOrder($orderId)
    {
        
        $order = new OrdersCaptureRequest($orderId);
        $order->prefer('return=representation');

        try {
            $response = $this->client->execute($order);
            
            // Save Customer Transaction
            $payment = new Payment;
            $payment->transaction_id = $response->result->id; //transaction id
            $payment->customer_id =  $response->result->purchase_units[0]->payee->merchant_id;
            $payment->customer_email =  $response->result->purchase_units[0]->payee->email_address;
            $payment->amount =  $response->result->purchase_units[0]->amount->value;
            $payment->currency =  $response->result->purchase_units[0]->amount->currency_code;
            $payment->transaction_status =  $response->result->status;
            $payment->save();

            return response()->json($response);
        }
        catch (HttpException $ex) {
            return $ex->getMessage();
        }
    }

    
}
