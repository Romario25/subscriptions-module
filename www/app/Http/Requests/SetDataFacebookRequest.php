<?php
namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class SetDataFacebookRequest extends FormRequest
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
            'bundle_version' => 'required|string',
            'advertiser_id' => 'required|string',
            'advertiser_tracking_enabled' => 'required|integer',
            'bundle_id' => 'required|string',
            'extinfo.dev_model_name' => 'required|string',
            'extinfo.cpu_cores' => 'required|integer',
            'extinfo.avl_storage_size' => 'required|integer',
            'extinfo.screen_density' => 'required|string',
            'extinfo.carrier_name' => 'required|string',
            'extinfo.locale' => 'required|string',
            'extinfo.screen_height' => 'required|integer',
            'extinfo.ext_info_ver' => 'required|string',
            'extinfo.pkg_ver_code' => 'required|string',
            'extinfo.pkg_info_ver_name' => 'required|string',
            'extinfo.ext_storage_size' => 'required|integer',
            'extinfo.os_ver' => 'required|string',
            'extinfo.app_pkg_name' => 'required|string',
            'extinfo.screen_width' => 'required|integer',
            'extinfo.dev_timezone_abv' => 'required|string',
            'extinfo.dev_timezone' => 'required|string',
            'bundle_short_version' => 'required|string',
            'application_tracking_enabled' => 'required|integer',
            'attribution' => 'required|string'
        ];
    }
}