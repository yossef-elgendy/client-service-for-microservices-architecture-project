<?php

namespace App\Http\Resources\Subscription;

use App\Models\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionIndexResoruce extends JsonResource
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
            'id' => $this->id,
            'nursery_id' => $this->nursery_id,
            'child_name' => $this->child->name,
            'status' => Subscription::SUBSCRIPTION_STATUS[$this->status ?? 0],
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'payment_date' => $this->payment_date,
            'payment_method' => Subscription::PAYMENT_METHOD[$this->payment_method ?? 0],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          ];
    }
}
