<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ObjectRequest extends FormRequest
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
            ->whereNotIn('object_id', ($this->route('object')) ? [$this->route('object')->object_id] : [])
            ->where('object_id', '!=', '0000') // make one id available
            ->pluck('object_id')->toArray();

        return [
            'customer_id' => 'required',
            'object_id' => [
                'required',
                Rule::notIn($takenObjectIds)
            ],
            'license_plate' => 'required',
            'spot' => 'required',
            'object_type_id' => 'required',
            'brand' => 'required',
            'width' => 'required',
            'length' => 'required'
        ];
    }

}
