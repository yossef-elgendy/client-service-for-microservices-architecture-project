<?php

namespace App\Http\Requests;

use App\Models\Child;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
        return[
            'nursery_id'=>'required|integer',
            'child_id'=>'required_without:name, age, gender|integer',
            'courses' => 'nullable|array',
            'activities' => 'nullable|array',
           
        ];
    }
}
