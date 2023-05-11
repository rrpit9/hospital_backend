<?php

namespace App\Http\Controllers\Api\Employee\V1;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserInfoResource;
use App\Http\Resources\V1\NotificationResource;

class AuthenticationController extends Controller
{
    public function login(Request $req)
    {
        $req->validate([
            'business_id' => 'required|integer|min:1',
            'email_mobile' => 'required|string',
            'password' => 'required|string',
            'device_type' => 'required|in:'.implode(',',device_type()),
            'device_token' => 'required|string'
        ],[
            'email_mobile.required' => 'The email or mobile field is required.'
        ]);

        $emailOrMobile = $req->email_mobile;
        $businessId = $req->business_id;
        $password = $req->password;

        $employee = Employee::where(function ($q) use ($emailOrMobile){
            $q->where('email', $emailOrMobile)->orWhere('mobile', $emailOrMobile);
        })->where('business_id', $businessId)->latest('id')->first();
        
        try{
            $response = $this->performUserLogin($employee, $password ,'employee');
            return $this->respondOk(new UserInfoResource($response));
        }catch(Exception $e){
            throw $e;
        }
    }

    public function getUserProfile(Request $req)
    {
        $employee = $req->user();
        
        return $this->respondOk(new UserInfoResource($employee));
    }

    public function getUserNotification(Request $req)
    {
        $employee = $req->user();
        $notifications = $employee->notifications;
        return $this->respondOk(NotificationResource::collection($notifications));
    }
}
