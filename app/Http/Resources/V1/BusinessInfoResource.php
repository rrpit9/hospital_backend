<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessInfoResource extends JsonResource
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
            'client_id' => $this->client_id,
            'business_name' => $this->name,
            'business_email' => $this->email,
            'logo' => $this->logo,
            'address' => $this->address,
            'pincode' => $this->pincode,
            'valid_till' => dateCheck($this->valid_till, true),
            'active' => (bool) $this->active
        ];
        return parent::toArray($request);
    }
}
