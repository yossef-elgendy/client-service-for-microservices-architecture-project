<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            "id"=>'required|unique:clients,id',
            // "fullname" => "required|string",
            "login_type"=> "required|in:".implode(',', array_keys(Client::LOGIN_TYPE)),
            "email"=>"required_if:login_type,EM|email|unique:clients,email",
            "phone"=>"required_if:login_type,MO|unique:clients,phone",
            "country"=> "max:30",
            "city"=> "max:30",
            "area"=> "max:30",
            "payment_info"=>"json",
         ];
    }
}
