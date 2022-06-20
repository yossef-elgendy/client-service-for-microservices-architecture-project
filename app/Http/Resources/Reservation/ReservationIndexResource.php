<?php

namespace App\Http\Resources\Reservation;


use App\Models\Client;
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
        $client = Client::find($this->client_id);
        return [
            'id'=>$this->id,
            'parent_name'=> $client->fullname,
            'nursery_id'=> $this->nursery_id,
            'timetable_id'=>$this->child->timetable_id ,
            'child_name'=> $this->child->name,
            'type' => Reservation::RESERVATION_TYPE[$this->type] ?? Reservation::RESERVATION_TYPE[0]  ,
            "status" => Reservation::RESERVATION_STATUS[$this->status] ?? Reservation::RESERVATION_STATUS[0],
            'reservation_start_date'=> $this->reservation_start_date,
            'provider_response'=> Reservation::PROVIDER_END[$this->provider_end] ?? Reservation::PROVIDER_END[0]
        ];
    }
}
