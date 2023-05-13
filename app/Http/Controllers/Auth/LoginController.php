<?php

namespace App\Http\Controllers\Auth;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\User as Admin;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:admin')->except('logout');
        $this->middleware('guest:client')->except('logout');
        $this->middleware('guest:employee')->except('logout');
        $this->middleware('guest:customer')->except('logout');
    }

    public function showLoginForm()
    {
        $userType = 'admin';
        return view('auth.login', compact('userType'));
    }

    public function login(Request $req)
    {
        $req->validate([
            'userType' => 'required|min:1|string|in:'.implode(',', userType()),
            'business_id' => 'required_if:userType,employee|numeric|min:1',
            'email_mobile' => 'required|string|min:10',
            'password' => 'required|string',
            'device_type' => 'required|in:'.implode(',',device_type()),
        ]);

        $userType = $req->userType;
        $businessId = $req->business_id ?? 0;
        $emailOrMobile = $req->email_mobile;
        $password = $req->password;

        switch($userType){
            case 'admin'    : $user = Admin::query();break;
            case 'client'   : $user = Client::query();break;
            case 'employee' : $user = Employee::where('business_id', $businessId);break;
            case 'customer' : $user = Customer::query();break;
        }

        $user = $user->where(function ($q) use ($emailOrMobile){
            $q->where('email', $emailOrMobile)->orWhere('mobile', $emailOrMobile);
        })->latest('id')->first();

        try{
            $response = $this->performUserLogin($user, $password ,$userType);
            return redirect()->intended("$userType/dashboard");
        }catch(Exception $e){
            throw $e;
        }
    }
}
