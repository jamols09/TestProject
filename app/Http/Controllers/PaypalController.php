<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//Paypal

use Session;
use Illuminate\Support\Facades\Redirect;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

class PaypalController extends Controller
{
    private $api_config;

    public function __construct()
    {
        $config = config('paypal');
        $this->api_config = new ApiContext(new OAuthTokenCredential($config['client_id'], $config['secret']));
        $this->api_config->setConfig($config['settings']);
    }

    public function index()
    {
       
        return view('payment.index');
    }

    public function submit(Request $request)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $product = new Item();
        $product->setName('Yoga Mat')->setCurrency(config('paypal.currency'))->setQuantity(1)->setPrice($request->input('amount'));

        $prodList = new ItemList();
        $prodList->setItems(array($product));

        $amount = new Amount();
        $amount->setCurrency(config('paypal.currency'))->setTotal($request->input('amount'));

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($prodList)
            ->setDescription('Good example');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(\URL::route('billing.status'))
            ->setCancelUrl(\URL::route('billing.status'));

        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)->setTransactions(array($transaction));

        try 
        {
            $payment->create($this->api_config);
        } 
        catch (\PayPal\Exception\PPConnectionException $e)
        {
            if (\Config::get('app.debug')) {
                Session::put('error','Connection timeout');
                return Redirect::route('paywithpaypal');                
            } else {
                Session::put('error','Some error occur, sorry for inconvenient');
                return Redirect::route('paywithpaypal');                
            }
        }

        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        
        Session::put('payment_id', $payment->getId());

        if(isset($redirect_url)) {            
            return Redirect::away($redirect_url);
        }

        Session::put('error','Unknown error occurred');

        return redirect()->back();
    }

    public function status(Request $request)
    {
        $payment_id = Session::get('payment_id');

        Session::forget('payment_id');
        if (empty($request->input('PayerID')) || empty($request->input('token'))) {
            Session::put('error','Payment failed');
            return redirect()->route('billing.payment');
        }
        $payment = Payment::get($payment_id, $this->api_config);        
        $execution = new PaymentExecution();
        $execution->setPayerId($request->input('PayerID'));        
        $result = $payment->execute($execution, $this->api_config);
        
        if ($result->getState() == 'approved') {         
            Session::put('success','Payment success !!');
            return redirect()->route('billing.payment');
        }

        Session::put('error','Payment failed !!');
		return redirect()->route('billing.payment');
    }
}
