<?php

namespace App\Http\Controllers\Iframe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function store(\App\Http\Requests\Iframe\AppointmentRequest $request, \App\Models\Company $company)
    {

        // object exist check
        $object = \App\Models\CustomerObject::whereObjectId($request->object_id)
            ->whereHas('customer', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->first();

        if (!$object) {
            return response()->json(['message' => __('global.appointment_object_not_found')], 404);
        }

        $request->merge(array('customer_object_id' => $object->id));

        $appointment = $company->appointments()->create($request->all());

        // send email to owner
        if ($company->id != 4) {
            if ($request->input('emails.owner')) {
                \Mail::to($company->email)->queue(new \App\Mail\Appointment\Created\Owner($appointment));
            }
        }

        // send email to customer
        if ($request->input('emails.customer')) {
            \Mail::to($object->customer->email)->queue(new \App\Mail\Appointment\Created\Customer($appointment));
        }

        return response()->json([
            'message' => 'Appointment added',
            'result' => new \App\Http\Resources\Appointment($appointment),
        ], 200);

    }

}
