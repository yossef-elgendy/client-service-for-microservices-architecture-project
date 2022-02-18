<?php

namespace App\Http\Resources;

use App\Models\Child;
use App\Models\Client;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildResource extends JsonResource
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
            'full_name' => $this->full_name,
            'age' => $this->age,
            'parent_name'=> Client::where('id', $this->client_id)->first()->full_name,
            "status" => Child::STATUS[$this->status] ?? Child::STATUS[0],
            "gender" => Child::GENDER[$this->gender] ?? Child::GENDER[0],
            'time_table' => $this->issues,
            'marks' => $this->marks,
            'rate' => $this->rate
        ];
    }
}
