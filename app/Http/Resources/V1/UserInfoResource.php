<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'mobile_verified_at' => dateCheck($this->mobile_verified_at, true),
            'email' => $this->email,
            'email_verified_at' => dateCheck($this->email_verified_at, true),
            'referral_code' => $this->referral_code,
            'image' => url("$this->image"),
            'gender' => $this->gender,
            'dob' => dateCheck($this->dob),
            'marital' => $this->marital,
            'aniversary' => dateCheck($this->aniversary),
            'is_registered' => (bool) $this->is_registered,
            'active' => (bool) $this->active,
            'last_login' => dateCheck($this->last_login, true)
        ];
        // Sending AccessToken at the Time of Login
        if($this->accessToken){
            $response['token_type'] = 'Bearer';
            $response['access_token'] = $this->accessToken;
        }
        return $response;
        return parent::toArray($request);
    }
}
