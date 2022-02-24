<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return response()->json([
            'status' => 'true',
            'result' => \App\Http\Resources\Customer::collection(\App\Models\Customer::get())
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(\App\Http\Requests\CustomerRequest $request)
    {

        $customer = new \App\Models\Customer();
        $customer->fill($request->all());
        $customer->company_id = Auth::user()->company->id;

        if (Auth::user()->company->cashier_stripe_key) {

            $stripeCustomer = $customer->createAsStripeCustomer();

            $customerOne = \App\Models\Customer::where('stripe_id', $stripeCustomer->id)->first();
        } else {
            $customer->save();
            $customerOne = \App\Models\Customer::latest()->first();
        }

        return response()->json([
            'status' => 'true',
            'result' => $customerOne,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\Customer $customer)
    {

        $customer->load('objects', 'objects.appointments');

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\Customer($customer)
        ]);
    }

    public function getByStripeId(Request $request, $stripe_id)
    {
        $customer = \App\Models\Customer::where('stripe_id', $stripe_id)->first();

        return response()->json([
            'status' => 'true',
            'result' => $customer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(\App\Http\Requests\CustomerRequest $request, \App\Models\Customer $customer)
    {

        $customer->fill($request->all());
        $customer->save();

        return response()->json([
            'status' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\App\Models\Customer $customer)
    {

        $customer->delete();

        $customer->objects()->delete();

        return response()->json([
            'status' => 'true'
        ]);
    }

    public function isStripeCustomer(Request $request, $customer_uuid)
    {
        $customer = \App\Models\Customer::whereUuid($customer_uuid)->first();
        $cashier_stripe_key = Auth::user()->company->cashier_stripe_key;
        if ($customer->stripe_id) {
            return response(['result' => true]);
        } else {
            return response(['result' => false]);
        }
    }

    public function createStripeCustomer(Request $request, $customer_uuid)
    {
        try {
            $customer = \App\Models\Customer::whereUuid($customer_uuid)->first();
            $customer->createAsStripeCustomer();

            return response(['result' => true]);
        } catch (\Throwable $th) {
            return response(['err' => $th->getMessage()], 500);
        }
    }
}
