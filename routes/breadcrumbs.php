<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

// Home > About
Breadcrumbs::for('billing.payment', function ($trail) {
    $trail->parent('home');
    $trail->push('Payment', route('billing.payment'));
});