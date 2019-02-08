<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetDataAppsflyerRequest extends FormRequest
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
            'udid' => 'required|string',
            'appsflyer_id' => 'required|string',
            'idfa' => 'required|string',
            'bundle_id' => 'required|string',
            'unique_id' => 'required|string'
        ];
    }
}
