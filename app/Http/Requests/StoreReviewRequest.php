<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormRequestPreventAutoValidation;

class StoreReviewRequest extends FormRequest
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
            'model_id' => 'required|integer',
            'model_type'=>'required|string|in:nursery,course',
            'client_id'=> 'required|integer',
            'content'=>'required|string|max:400',
            'rate'=>'required|integer|between:1,5'
        ];
    }
}
