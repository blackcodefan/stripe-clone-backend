<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Customer;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // validate old password
        Validator::extend('passcheck', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, Auth::user()->getAuthPassword());
        });

        Cashier::useCustomerModel(Customer::class);
    }
}
