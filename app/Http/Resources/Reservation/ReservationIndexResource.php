<?php

namespace App\Http\Resources\Reservation;

use App\Models\Child;
use App\Models\Client;
use App\Models\Order;
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
        $order = Order::where('reservation_id', $this->id)->first();
        return [
            'id'=>$this->id,
            'parent_name'=> $client->fullname,
            'nursery_id'=> $this->nursery_id,
            'timetable_id'=>$this->child->timetable_id,

            'order_id'=> $order->id,
            'price'=> $order->totalCost,

            'child'=> [
                'name' => $this->child->name,
                'age'=> $this->child->age,
                'gender'=> Child::GENDER[$this->child->gender ?? 0],
                'id'=> $this->child->id
            ],

            'type' => Reservation::RESERVATION_TYPE[$this->type] ?? Reservation::RESERVATION_TYPE[0] ,
            "status" => Reservation::RESERVATION_STATUS[$this->status] ?? Reservation::RESERVATION_STATUS[0],
            'reservation_start_date'=> $this->reservation_start_date,

            'provider_response'=> Reservation::PROVIDER_END[$this->provider_end] ?? Reservation::PROVIDER_END[0],
            'client_response' => Reservation::CLIENT_END[$this->client_end] ?? Reservation::CLIENT_END[0],
            
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
