<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChildRequest extends FormRequest
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
            'name'=>'required|string|max:30',
            'age'=> 'required|integer|max:13',
            'nursery_id'=>'nullable|integer',
            'time_table'=> 'nullable|array|min:3',
            'time_table.*'=>'nullable|date',
            'gender' => 'required|integer',
            'mediafile' => 'nullable|file|mimes:jpg,bmp,png',
        ];
    }
}
