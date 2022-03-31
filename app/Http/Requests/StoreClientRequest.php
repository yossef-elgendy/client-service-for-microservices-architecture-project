<?php

namespace App\Http\Requests;

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
            "fullname" => "required|string",
            "mobile_number"=>"string|unique:clients,mobile_number|min:11|max:11",
            "country"=> "max:30",
            "city"=> "max:30",
            "area"=> "max:30",
            "payment_info"=>"json",
         ];
    }
}
