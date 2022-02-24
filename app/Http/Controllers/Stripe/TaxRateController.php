<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $tax_rates = Cashier::stripe()->taxRates->all();

        return response(['result' => $tax_rates]);

    }

}
