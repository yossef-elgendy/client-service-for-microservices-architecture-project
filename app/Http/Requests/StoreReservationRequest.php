<?php

namespace App\Http\Requests;

use App\Models\Child;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormRequestPreventAutoValidation;

class StoreReservationRequest extends FormRequest
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
        return[
            'nursery_id'=>'required|integer',
            'child_id'=>'required|integer',
            'courses' => 'nullable|array',
            'activities' => 'nullable|array',
            'client_id'=>'required|integer'
        ];
    }
}
