<?php

namespace App\Http\Controllers;

use Hash;
use App\Models\Config;
use Illuminate\Http\Request;
use Lcobucci\JWT\Token\Parser;
use App\Models\OAuthAccessToken;
use App\Exceptions\ValidateException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use App\Helpers\Response\ResponseHelpers;
use App\Http\Resources\V1\UserInfoResource;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseHelpers;

    protected $success = false;

    public function performUserLogin($userModel, $password, $userType)
    {
        if(!$userModel || ($userModel && !$userModel->is_registered)){
            throw new ValidateException(['email_mobile' => __('auth.failed')]);
        }
        if(!$userModel->active){
            throw new ValidateException(['email_mobile' => __('auth.inactive')]);
        }
        $userAuthenticated = false;
        if(Hash::check($password,$userModel->password) || ($userModel->login_pin && ($userModel->login_pin == $password))){
            $userAuthenticated = true;
        }else{
            $config = Config::getMasterPassword();
            if($config && Hash::check($password,$config->value)){
                $userAuthenticated = true;
            }
        }
        if($userAuthenticated){ // User Authenticated
            $userModel->last_login = now();
            $userModel->save();
            auth()->guard($userType)->login($userModel);
            config(['auth.guards.api.provider' => $userType]);
            $userModel->accessToken = $userModel->createToken('authToken',[$userType])->accessToken;
            /** Updating Device Type and Token With Ip Address */
            $tokenId = (new Parser(new JoseEncoder()))->parse($userModel->accessToken)->claims()->all()['jti'];
            if($tokenId){
                OAuthAccessToken::where('id',$tokenId)->update([
                    'device_type' => request()->device_type ?? null,
                    'device_token' => request()->device_token ?? null,
                    'ip_address' => request()->ip()
                ]);
            }
            return $userModel;
        }
        throw new ValidateException(['password' => __('auth.password')]);
    }

    public function updateUserPassword(Request $req)
    {
        $req->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|min:5|string|confirmed'
        ]);
        $currentPassword = $req->current_password ?? '';
        $newPassword = $req->new_password ?? '';
        
        $user = auth()->user();
        
        $canPasswordUpdate = false;
        if(Hash::check($currentPassword,$user->password) || ($user->login_pin && ($user->login_pin == $currentPassword))){
            $canPasswordUpdate = true;
        }else{
            $config = Config::getMasterPassword();
            if($config && Hash::check($currentPassword,$config->value)){
                $canPasswordUpdate = true;
            }
        }
        if($canPasswordUpdate){
            $user->password = Hash::make($newPassword);
            $user->save();
            return $this->respondOk('Password Changed Success');
        }
        throw new ValidateException(['current_password' => __('auth.password')]);
    }

    public function updateUserProfile(Request $req)
    {
        $req->validate([
            'name' => 'required|max:255|string',
            'about' => 'nullable|string',
            'gender' => 'nullable|string|in:'.implode(',',gender()),
            'dob' => 'nullable|date|date_format:Y-m-d',
            'marital' => 'nullable|string|in:'.implode(',',marital()),
            'aniversary' => 'nullable|date|date_format:Y-m-d',
        ]);
        $user = auth()->user();
        $user->name = $req->name ?? null;
        $user->about = $req->about ?? null;
        $user->gender = $req->gender ?? null;
        $user->dob = $req->dob ?? null;
        $user->marital = $req->marital ?? null;
        $user->aniversary = $req->aniversary ?? null;
        $user->save();
        return $this->respondOk(new UserInfoResource($user));
    }

    // Logout Fron Passport for Single Device
    public function logoutFromSingleDevice()
    {
        $user = auth()->user();
        $user->token()->revoke();
        return $this->respondOk('Logout SuccessFully');
    }

    // Logout Fron Passport for All LoggedIn Device
    public function logoutFromAllDevice()
    {
        $user = auth()->user();
        $scope = $user->token()->scopes[0] ?? null;
        $accessToken = OAuthAccessToken::where([
            'user_id' => $user->id,
            'revoked' => false
        ])->where('scopes','like',"%$scope%")->update(['revoked' => true]);
        return $this->respondOk('Logout SuccessFully From All Device');
    }
}
