<?php

namespace App\Http\Requests;

use App\Models\Child;
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
            'client_id'=>'required|integer',
            'nursery_id'=>'nullable|integer',
            'time_table'=> 'nullable|array|min:3',
            'time_table.*'=>'nullable|date',
            'gender' => 'required|integer|in:'.implode(',', array_keys(Child::GENDER)),
            'mediafile' => 'nullable|file|mimes:jpg,bmp,png',
        ];
    }
}
