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
            'parent_name'=> auth()->user()->full_name,
            'nursery_id'=> $this->nursery_id,
            'child name'=> Child::find($this->child_id)->name,
            "status" => Reservation::STATUS[$this->status] ?? Reservation::STATUS[0],
        ];
    }
}
