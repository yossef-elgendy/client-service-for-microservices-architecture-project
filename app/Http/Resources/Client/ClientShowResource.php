<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\Child\ChildIndexResource;
use App\Http\Resources\Reservation\ReservationIndexResource;
use App\Models\Client;
use App\Models\Media;
use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientShowResource extends JsonResource
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
        $profile_image = Media::whereHasMorph(
            'mediafileable',
            [Client::class],
            function(Builder $query) {
              $query->where([
                ['model_id', '=', $this->id],
                ['type', '=', 'profile_image']
              ]);
            }
          )->first();

        $children = ChildIndexResource::collection($this->children);
        $reservations = ReservationIndexResource::collection($this->reservations);

        return [
            "username" => $this->username,
            "full_name" => $this->full_name,
            "email" => $this->email,
            "status" => Client::STATUS[$this->status] ?? Client::STATUS[0],
            "gender" => Client::GENDER[$this->gender] ?? Client::GENDER[0],
            "location"=> $this->location,
            'profile_image' => $this->mediafileDownload($profile_image),
            'children' => $children,
            'reservations'=> $reservations
        ];
    }
}
