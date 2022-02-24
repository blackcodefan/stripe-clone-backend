<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertProspectRequest extends FormRequest
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

        // get taken object_ids
        $takenObjectIds = \App\Models\CustomerObject::whereHas('customer', function($q) {
            $q->where('company_id', \Auth::user()->company->id);
        })
            ->where('object_id', '!=', '0000') // make one id available
            ->pluck('object_id')->toArray();

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
            'spot' => 'required',
            'object_id' => [
                'required',
                Rule::notIn($takenObjectIds)
            ],
        ];
    }
}
