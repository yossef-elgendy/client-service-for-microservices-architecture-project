<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FormRequestPreventAutoValidation;

class UpdateReservationRequest extends FormRequest
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
        return  [
            'client_end' => 'required_if:status,0,2|in:'.implode(',', array_keys(Reservation::CLIENT_END)),
            'client_id'=> 'required|integer'
          ];
    }
}
