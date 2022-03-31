<?php

namespace App\Http\Resources\Review;

use App\Models\Client;
use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'id' => $this->id,
            'client_fullname' => $client->fullname,
            'rate' => $this->rate,
            'content' => $this->content,
            'type' => Review::TYPE[$this->model_type]
        ];
    }
}
