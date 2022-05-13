<?php

namespace App\Http\Resources\Client;

use App\Models\Client;
use App\Models\Media;
use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientIndexResource extends JsonResource
{
    use Helpers;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            "id"=>$this->id,
            "fullname" => $this->fullname,
            "email"=>$this->email,
            "phone"=>$this->phone,
            "login_type"=> $this->login_type,
            "status" => Client::STATUS[$this->status] ?? Client::STATUS[0],
            "gender" => Client::GENDER[$this->gender] ?? Client::GENDER[0],
            "location"=> $this->location,
        ];
    }
}
