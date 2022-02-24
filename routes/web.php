<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

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

Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook']);

Auth::routes(['verify' => true, 'login' => false]);

Route::post('/login', function () {

    $email = \Request::get('email');
    $password = \Request::get('password');
    $remember_me = \Request::get('remember_me');

    if (Auth::attempt([
        'email' => $email,
        'password' => $password
    ], $remember_me)) {

        return response()->json([], 204);
    } else {
        return response()->json([
            'error' => __('auth.failed')
        ], 403);
    }
});

Route::get('/', function () {
    return view('welcome');
});
