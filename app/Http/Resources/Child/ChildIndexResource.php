<?php

namespace App\Http\Resources\Child;

use App\Models\Child;
use App\Models\Client;
use App\Models\Media;
use App\Traits\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildIndexResource extends JsonResource
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
            [Child::class],
            function(Builder $query) {
              $query->where([
                ['model_id', '=', $this->id],
                ['type', '=', 'profile_image']
              ]);
            }
          )->first();


        $client =  Client::findOrFail($this->client_id);
        return [
            "id"=>$this->id,
            'name' => $this->name,
            'age' => $this->age,
            'parent_name'=> $client->fullname,
            'client_id'=> $this->client_id,
            "status" => Child::STATUS[$this->status] ?? Child::STATUS[0],
            "gender" => Child::GENDER[$this->gender] ?? Child::GENDER[0],
            'timetable_id'=>$this->timetable_id,
            'marks' => $this->marks,
            'rate' => $this->rate,
            'profile_image' => $this->mediafileDownload($profile_image)
        ];
    }
}
