@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{ Breadcrumbs::render('billing.checkout') }}
            <div class="card">
                <div class="card-header">
                    Checkout
                </div>

                <div class="card-body">
                    <div class="col-md-12">
                        <div class="col-lg-3 col-md-3 col-sm-4 col-6 text-center">
                                      
                            <a href="/">
                                <figure class="figure">
                                    <img src="{{asset('images/mat-img.jpg')}}" width="100" height="90" class="rounded" alt="Yoga Mattress">
                                    <figcaption class="figure-caption text-center">Yoga Mat (Cyan)</figcaption>
                                    <figcaption class="figure-caption text-center">&#8369; 422.00</figcaption>
                                </figure>
                                <div id="paypal-button-container"></div>
                            </a>
                            
                        </div>
                        <div class="col-md-4">
                            
                        </div>
                    </div>
                    <script src="https://www.paypal.com/sdk/js?client-id={!! config('paypal.client_id') !!}&currency={!! config('paypal.currency') !!}"></script>
                    <script>
                        
                        paypal.Buttons({
                            style: {
                                shape:  'pill',
                                layout: 'horizontal',
                                color:  'blue',
                                label:  'pay',
                                height: 35
                            },
                            // Call your server to set up the transaction
                            createOrder: function(data, actions) {
                                return fetch("{!! route('billing.createOrder') !!}", {
                                    method: 'post',
                                    headers: {
                                        'content-type': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                }).then(function(res) {
                                    return res.json();
                                }).then(function(orderData) {
                                    return orderData.result.id;
                                });
                            },

                            // createOrder: function(data, actions) {
                            //     return actions.order.create({
                            //         purchase_units: [{
                            //             amount: {
                            //                 value: 420.00,
                            //                 currency_code: "PHP"
                            //             }
                            //         }]
                            //     });
                            // },
                
                            // Call your server to finalize the transaction
                            onApprove: function(data, actions) {
                                return fetch('captureOrder/' + data.orderID,  {
                                    method: 'post',
                                    headers: {
                                        'content-type': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                }).then(function(res) {
                                    return res.json();
                                }).then(function(orderData) {
                                    // Three cases to handle:
                                    //   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
                                    //   (2) Other non-recoverable errors -> Show a failure message
                                    //   (3) Successful transaction -> Show confirmation or thank you

                                    // This example reads a v2/checkout/orders capture response, propagated from the server
                                    // You could use a different API or structure for your 'orderData'
                                    var errorDetail = Array.isArray(orderData.details) && orderData.details[0];

                                    if (errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED') {
                                        return actions.restart(); // Recoverable state, per:
                                        // https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
                                    }

                                    if (errorDetail) {
                                        var msg = 'Sorry, your transaction could not be processed.';
                                        if (errorDetail.description) msg += '\n\n' + errorDetail.description;
                                        if (orderData.debug_id) msg += ' (' + orderData.debug_id + ')';
                                        return alert(msg); // Show a failure message
                                    }

                                    // Show a success message
                                    console.log(orderData)
                                    alert('Transaction completed by ' + orderData.result.payer.name.given_name);
                                   
                                    // return fetch('savePaymentInfo/' + orderData.result.id, {
                                    //     method: 'get',
                                    //     headers: {
                                    //         'content-type': 'application/json',
                                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    //     },
                                    // })
                                    // .then(function(data) {
                                    //     console.log("Payee Details: ",data)
                                    // })
                                });
                            }
                        }).render('#paypal-button-container');
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
