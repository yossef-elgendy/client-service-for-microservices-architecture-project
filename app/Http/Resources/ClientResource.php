<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            "username" => $this->username,
            "full_name" => $this->full_name,
            "email" => $this->email,
            "status" => Client::STATUS[$this->status] ?? Client::STATUS[0],
            "gender" => Client::GENDER[$this->gender] ?? Client::GENDER[0],
            "location"=> $this->location,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at
        ];
    }
}
