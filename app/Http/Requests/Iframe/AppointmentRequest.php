<?php

namespace App\Http\Requests\Iframe;

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
        return [
            'object_id' => 'required',
            'type' => 'required',
            'name' => 'required',
            'appointment_at' => 'required',
            'email' => 'required|email',
            //'note' => 'required'
        ];
    }
}
