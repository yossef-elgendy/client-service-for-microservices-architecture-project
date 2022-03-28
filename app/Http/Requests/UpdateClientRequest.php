<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
            'username' => 'unique:clients,username|nullable',
            'password' => 'nullable',
            'fullname' => 'nullable',
            'mobile_number' => 'nullable|unique:clients,mobile_number',
            'gender' => 'nullable|in:0,1',
            'mediafile' => 'nullable|file|mimes:jpg,bmp,png',
        ];
    }
}
