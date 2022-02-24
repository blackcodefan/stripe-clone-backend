<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProspectController extends Controller
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
            'result' => \App\Http\Resources\Prospect::collection(\App\Models\Prospect::all())
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
    public function store(\App\Http\Requests\ProspectRequest $request)
    {

        $prospect = new \App\Models\Prospect();
        $prospect->fill($request->all());
        $prospect->company_id = \App\Models\Company::whereUuid($request->uuid)->first()->id; // TODO: fix, find at top and return error
        $prospect->save();

        // send email to owner
        if($request->input('emails.owner')) {
            \Mail::to($prospect->company->email)->queue(new \App\Mail\Prospect\Created\Owner($prospect));
        }

        // send email to customer
        if($request->input('emails.customer')) {
            \Mail::to($prospect->email)->queue(new \App\Mail\Prospect\Created\Customer($prospect));
        }

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\Prospect($prospect)
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\Prospect $prospect)
    {

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\Prospect($prospect)
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
    public function update(Request $request, \App\Models\Prospect $prospect)
    {

        $prospect->fill($request->all());
        $prospect->save();

        return response()->json([
            'status' => true,
            'result' => new \App\Http\Resources\Prospect($prospect)
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\App\Models\Prospect $prospect)
    {

        $prospect->delete();

        return response()->json([
            'status' => 'true'
        ]);

    }

    public function convert(\App\Http\Requests\ConvertProspectRequest $request, \App\Models\Prospect $prospect)
    {

        \DB::beginTransaction();

        try {

            // try to create customer
            $customer = \Auth::user()->company->customers()->create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'street' => $request->street,
                'number' => $request->number,
                'zipcode' => $request->zipcode,
                'city' => $request->city,
            ]);

            // try to add object
            $object = $customer->objects()->create([
                'brand' => $request->brand,
                'type' => $request->type,
                'license_plate' => $request->license_plate,
                'width' => $request->width,
                'length' => $request->length,
                'object_id' => $request->object_id,
                'spot' => $request->spot,
                'object_type_id' => $request->object_type_id
            ]);

            \DB::commit();

            // make sure we set status to "converted"
            $prospect->status_id = 3;
            $prospect->object_id = $object->id;
            $prospect->save();

            return response()->json([
                'status' => true,
                'customer' => new \App\Http\Resources\Customer($customer)
            ]);

        } catch (\Exception $e) {

            report($e); // write to Telescope // $e->getMessage() this is the exact error

            \DB::rollback();

            return response()->json([
                'status' => 'error',
                'errors' => ['Something went wrong while converting this prospect, please contact us'],
            ], 422);

        }


    }

}
