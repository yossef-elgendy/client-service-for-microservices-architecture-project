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
            'username' => 'nullable|unique:clients,username,'.$this->route('id').',id',
            'fullname' => 'nullable',
            'login_type'=>'nullable|in:'.implode(',', array_keys(Client::LOGIN_TYPE)),
            'phone' => 'nullable|unique:clients,phone,'.$this->route('id').',id',
            "email"=> 'nullable|unique:clients,email,'.$this->route('id').',id',
            'gender' => 'nullable|in:0,1',
        ];
    }
}
