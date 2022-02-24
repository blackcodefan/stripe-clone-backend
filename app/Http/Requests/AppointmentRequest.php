<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        switch($this->method()) {

            case 'POST':
                return [
                    'customer_object_id' => 'required',
                    'type' => 'required',
                    'appointment_at' => 'required'
                    //'note' => 'required'
                ];

            case 'PATCH':
                return [
//                    'uuid' => 'required',
//                    'object_id' => 'required',
//                    'type' => 'required',
//                    'name' => 'required',
//                    'appointment_at' => 'required',
//                    'email' => 'required|email',
                    //'note' => 'required'
                ];

            default:
                break;


        }

    }
}
