<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
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
        return  [
            'status' => 'required|in:'.implode(',', array_keys(Reservation::RESERVATION_STATUS)),
            'client_end' => 'required_if:status,0,2|in:'.implode(',', array_keys(Reservation::CLIENT_END)),
          ];
    }
}
