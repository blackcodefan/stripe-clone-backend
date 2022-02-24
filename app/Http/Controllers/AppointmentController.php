<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $appointments = \App\Models\Appointment::with('object', 'object.customer')
            ->when($request->status, function ($query, $status) {
                return $query->whereIn('status_id', json_decode($status));
            })
            ->when($request->date, function ($query, $date) {
                return $query->whereDate('appointment_at', $date);
            })
            ->get();

        return response()->json([
            'status' => 'true',
            'result' => \App\Http\Resources\Appointment::collection($appointments),
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
    public function store(\App\Http\Requests\AppointmentRequest $request)
    {

        $object = \App\Models\CustomerObject::find($request->customer_object_id);

        if (!$object) {
            return response()->json(['message' => __('global.appointment_object_not_found')], 404);
        }

        // set appointment to "Attention needed" - status 2 ?
        if ($request->note) {
            $request->merge(array('status_id' => 2));
        }

        // add e-mail; its mandatory
        $request->merge(array('email' => $object->customer->email, 'name' => $object->customer->full_name));

        $appointment = \Auth::user()->company->appointments()->create($request->all());

        // send email to owner
        if (\Auth::user()->company_id != 4) {
            if ($request->input('emails.owner')) {
                \Mail::to(\Auth::user()->company->email)->queue(new \App\Mail\Appointment\Created\Owner($appointment));
            }
        }

        // send email to customer
        if ($request->input('emails.customer')) {
            \Mail::to($object->customer->email)->queue(new \App\Mail\Appointment\Created\Customer($appointment));
        }

        return response()->json([
            'message' => 'Appointment added',
            'result' => new \App\Http\Resources\Appointment($appointment->load('object', 'object.customer'))
        ], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(\App\Models\Appointment $appointment)
    {

        $appointment->load('object.customer');

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\Appointment($appointment),
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
    public function update(\App\Http\Requests\AppointmentRequest $request, \App\Models\Appointment $appointment)
    {

        $appointment->fill($request->all());
        $appointment->save();

        // any objects to update?
        if ($request->has('object')) {
            $appointment->object()->update(['spot' => $request->object['spot']]);

            // update object status Id when appointment is processed
            if ($request->status_id == 3) {
                $appointment->object()->update(['status' => $request->type]);
            }
        }

        $appointment->load('object.customer');

        return response()->json([
            'status' => 'true',
            'requ' => $request->all(),
            'result' => new \App\Http\Resources\Appointment($appointment),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\App\Models\Appointment $appointment)
    {

        $appointment->delete();

        return response()->json([
            'status' => 'true',
        ]);

    }
}
