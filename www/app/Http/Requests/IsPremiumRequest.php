<?php
/**
 * Created by PhpStorm.
 * User: ataman
 * Date: 11.03.19
 * Time: 23:32
 */

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class IsPremiumRequest extends FormRequest
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
            'bundle_id' => 'required|string',
        ];
    }
}
