<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormRequestPreventAutoValidation;

class UpdateClientRequest extends FormRequest
{
    use FormRequestPreventAutoValidation;
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
            "username" => 'string|unique:clients,username,'.$this->route('id').',id',
            "fullname" => 'string',
            "gender"=>'in:'.implode(',', array_keys(Client::GENDER)),
            "email"=>"email|unique:clients,email".$this->route('id').',id',
            "phone"=>"unique:clients,phone".$this->route('id').',id',
            "governerate"=> "max:30",
            "city"=> "max:30",
            "area"=> "max:30",
        ];
    }
}
