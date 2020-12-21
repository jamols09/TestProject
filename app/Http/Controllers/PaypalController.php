<?php

namespace App\Http\Controllers;

use App\Models\Item as ModelsItem;
use Illuminate\Http\Request;


use Session;
use Illuminate\Support\Facades\Redirect;
//Paypal
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Item as PaypalItem;
use PayPal\Api\ItemList;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use Illuminate\Support\Facades\URL;
//Models
use App\Models\Item;

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
        $Items = Item::get();
        return view('payment.index', compact('Items'));
    }

    public function submit(Request $request)
    {

        $item = Item::where('id',$request->item_id)->first();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
      
        $product = new PaypalItem();
        $product->setName($item->name)->setCurrency(config('paypal.currency'))->setQuantity(1)->setPrice($item->price); //one at a time

        $prodList = new ItemList();
        $prodList->setItems(array($product));

        $amount = new Amount();
        $amount->setCurrency(config('paypal.currency'))->setTotal($item->price);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($prodList)
            ->setDescription($item->name);

        

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('billing.status')) //If payment is successful
                    ->setCancelUrl(URL::route('billing.status')); //If payment is cancelled

        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)->setTransactions(array($transaction));

        try 
        {
            $payment->create($this->api_config);
        }
        catch (\PayPal\Exception\PPConnectionException $e)
        {
            if (config('app.debug')) {
                Session::put('error','Connection timeout');
                return Redirect::route('paywithpaypal');                
            } else {
                Session::put('error','Backend Error');
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

        return redirect()->route('billing.payment');
    }

    public function status(Request $request)
    {
       
        $payment_id = Session::get('payment_id');
        
        Session::forget('payment_id');
        if (empty($request->input('PayerID')) || empty($request->input('token'))) {
            Session::put('error','Purchase failed.');
            return redirect()->route('home');
        }
        
        $payment = Payment::get($payment_id, $this->api_config);        
        $execution = new PaymentExecution();
        $execution->setPayerId($request->input('PayerID'));        
        $result = $payment->execute($execution, $this->api_config);
       
        if ($result->getState() == 'approved') {         
            Session::put('success','Purchase Success!');
            return redirect()->route('home');
        }

        Session::put('error','Payment failed !!');

		return redirect()->route('home');
    }
}
