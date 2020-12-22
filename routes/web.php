<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');

// Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::prefix('billing')->group(function () {
        Route::name('billing.')->group( function () {
            Route::get('/payment', [App\Http\Controllers\PaypalController::class, 'index'])->name('payment');
            Route::post('/payment', [App\Http\Controllers\PaypalController::class, 'submit'])->name('submit');
            Route::get('/status', [App\Http\Controllers\PaypalController::class, 'status'])->name('status');
            Route::get('/checkout', [App\Http\Controllers\PaypalCheckoutController::class, 'index'])->name('checkout');
            Route::post('/createOrder', [App\Http\Controllers\PaypalCheckoutController::class, 'createOrder'])->name('createOrder');
            Route::post('/captureOrder/{orderId}', [App\Http\Controllers\PaypalCheckoutController::class, 'captureOrder'])->name('captureOrder');
        });
    });
// });
// Route::group(['middleware' => 'guest'], function() {
//     Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
// });