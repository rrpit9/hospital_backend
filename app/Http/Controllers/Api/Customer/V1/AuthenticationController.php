<?php

namespace App\Http\Controllers\Api\Customer\V1;

use Exception;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserInfoResource;

class AuthenticationController extends Controller
{
    public function login(Request $req)
    {
        $req->validate([
            'email_mobile' => 'required|string',
            'password' => 'required|string',
            'device_type' => 'required|in:'.implode(',',device_type()),
            'device_token' => 'required|string'
        ],[
            'email_mobile.required' => 'The email or mobile field is required.'
        ]);

        $emailOrMobile = $req->email_mobile;
        $password = $req->password;

        $customer = Customer::where(function ($q) use ($emailOrMobile){
            $q->where('email', $emailOrMobile)->orWhere('mobile', $emailOrMobile);
        })->latest('id')->first();
        
        try{
            $response = $this->performUserLogin($customer, $password ,'customer');
            return $this->respondOk(new UserInfoResource($response));
        }catch(Exception $e){
            throw $e;
        }

    }

    public function getUserProfile(Request $req)
    {
        $user = $req->user();
        
        return $this->respondOk(new UserInfoResource($user));
    }
}
