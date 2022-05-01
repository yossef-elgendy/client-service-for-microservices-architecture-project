<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormRequestPreventAutoValidation;

class UpdateChildRequest extends FormRequest
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
            'name'=>'nullable|string|max:30',
            'client_id'=>'required|integer',
            'age'=> 'nullable|integer|max:13',
            'nursery_id'=>'nullable|integer',
            'gender' => 'nullable|integer',
            'mediafile' => 'nullable|file|mimes:jpg,bmp,png',
        ];
    }
}
