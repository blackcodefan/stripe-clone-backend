<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProspectRequest extends FormRequest
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
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'street' => 'required',
            'number' => 'required',
            'zipcode' => 'required',
            'city' => 'required',
            'license_plate' => 'required',
            'brand' => 'required',
            'type' => 'required',
            'length' => 'required',
            'width' => 'required',
            'object_type_id' => 'required',
            'delivery_at' => 'required',
            'status_id' => 'required'
        ];
    }
}
