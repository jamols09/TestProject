<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Home > Payment
Breadcrumbs::for('billing.payment', function ($trail) {
    $trail->parent('home');
    $trail->push('Payment', route('billing.payment'));
});

// Home > Checkout
Breadcrumbs::for('billing.checkout', function ($trail) {
    $trail->parent('home');
    $trail->push('Checkout', route('billing.checkout'));
});