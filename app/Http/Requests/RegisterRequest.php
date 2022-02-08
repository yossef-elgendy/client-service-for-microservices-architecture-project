<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            "username" => "required|string",
            "full_name" => "required|string",
            "email" => "required|string|unique:clients,email",
            "mobile_number"=>"required|string|unique:clients,mobile_number|min:11|max:11",
            "password"=> "required|string|min:8|confirmed",
            "country"=> "required|max:30",
            "city"=> "required|max:30",
            "area"=> "required|max:30",
            "payment_info"=>"required|json",
            'image' => 'image|mimes:jpeg,png,jpg,svg|max:2048',
         ];
    }
}
