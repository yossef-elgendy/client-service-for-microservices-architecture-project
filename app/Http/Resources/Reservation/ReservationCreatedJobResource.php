<?php

namespace App\Http\Resources\Reservation;

use App\Models\Child;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationCreatedJobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $child = Child::find($this->child_id);
        return [
            'reservation_id' => $this->id,
            'client_id'=> $this->client_id,
            'nursery_id'=> $this->nursery_id,

            'status' => $this->status,
            'client_end'=> $this->client_end,

            'child_id'=> $child->id,
            'name'=> $child->name,
            'age'=> $child->age,
            'rate'=> $child->rate,
            'gender'=> $child->gender

        ];
    }
}
