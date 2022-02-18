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
            'full_name'=>'string|max:30',
            'age'=> 'integer|max:13',
            'nursery_id'=>'integer',
            'time_table'=> 'array|min:3',
            'time_table.*'=>'date',
            'gender' => 'required|integer'
        ];
    }
}
