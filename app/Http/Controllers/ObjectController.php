<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ObjectController extends Controller
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
            'result' => \App\Http\Resources\CustomerObject::collection(\App\Models\CustomerObject::with('customer','appointments')->get()),
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
    public function store(\App\Http\Requests\ObjectRequest $request)
    {

        // find customer
        $customer = \App\Models\Customer::whereUuid($request->customer_id)->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
            ], 403);
        }

        $object = $customer->objects()->create($request->all());

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\CustomerObject($object),
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\CustomerObject $object)
    {

        $object->load('customer', 'appointments');

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\CustomerObject($object),
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
    public function update(\App\Http\Requests\ObjectRequest $request, \App\Models\CustomerObject $object)
    {

        $data = $request->all();
        $data['status'] = $data['status']['id'];
        $object->fill($data);
        $object->save();

        return response()->json([
            'status' => true,
            'result' => new \App\Http\Resources\CustomerObject($object),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\App\Models\CustomerObject $object)
    {

        $object->delete();

        return response()->json([
            'status' => 'true',
        ]);

    }
}
