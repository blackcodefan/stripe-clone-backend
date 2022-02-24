<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureStripeConfig;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware(['auth:sanctum', EnsureStripeConfig::class])->group(function () {

    // Route::group(['middleware' => 'auth:sanctum'], function () {

    // sync prices
    Route::get('/sync-prices', ['uses' => 'StripeSyncController@syncPrices']);

    // sync products
    Route::get('/sync-products', ['uses' => 'StripeSyncController@syncProducts']);

    // user info
    Route::get('/user', function (Request $request) {
        return new \App\Http\Resources\User($request->user());
    });

    // prices
    Route::resource('prices', 'PriceController');

    // products
    Route::resource('products', 'ProductController');

    // Stripe folder
    Route::prefix('stripe')->group(function () {
        Route::resource('tax_rates', 'Stripe\TaxRateController')->only(['index']);
    });

    // subscriptions
    Route::resource('subscriptions', 'SubscriptionController');

    // invoices
    Route::get('invoices/send/{id}', ['uses' => 'InvoiceController@send']);
    Route::resource('invoices', 'InvoiceController');

    // customers
    Route::get('customers/by-stripe-id/{stripe_id}', ['uses' => 'CustomerController@getByStripeId']);
    Route::post('customers/{id}/create-stripe-customer', ['uses' => 'CustomerController@createStripeCustomer']);
    Route::get('customers/{id}/is-stripe-customer', ['uses' => 'CustomerController@isStripeCustomer']);
    Route::resource('customers', 'CustomerController');

    // appointments
    Route::resource('appointments', 'AppointmentController');

    // objects
    Route::resource('objects', 'ObjectController');

    // prospects
    Route::post('prospects/{prospect}/convert', ['uses' => 'ProspectController@convert']);
    Route::resource('prospects', 'ProspectController')->except(['store']);

    Route::put('password', ['uses' => 'PasswordController@update']);

    // notes
    Route::apiResource('notes', 'NoteController');

    // company
    Route::get('company/is-stripe-configured', ['uses' => 'CompanyController@isStripeConfigured']);
});

// statuses
Route::resource('statuses', 'StatusController')->only(['index']);

// object types
Route::resource('object_types', 'ObjectTypeController');

// iframe show
Route::get('iframe/{uuid}', ['uses' => 'IframeController@show']);

// appointment store (no auth check)
Route::post('iframe/appointments/{company}', ['uses' => 'Iframe\AppointmentController@store']);

// prospect store (no auth check)
Route::post('prospects', ['uses' => 'ProspectController@store']);
