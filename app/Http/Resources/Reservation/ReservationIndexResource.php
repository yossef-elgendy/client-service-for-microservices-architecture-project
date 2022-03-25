<?php

namespace App\Http\Resources\Reservation;

use App\Models\Child;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'parent_name'=> $request->user()->firstname.' '.$request->user()->lastname,
            'nursery_id'=> $this->nursery_id,
            'child_name'=> Child::find($this->child_id)->name,
            "status" => Reservation::RESERVATION_STATUS[$this->status] ?? Reservation::RESERVATION_STATUS[0],
            'provider_response'=> Reservation::PROVIDER_END[$this->provider_end] ?? Reservation::PROVIDER_END[0]
        ];
    }
}
