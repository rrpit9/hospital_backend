<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Models\Employee;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ValidateException;
use App\Http\Resources\V1\UserInfoResource;
use App\Http\Resources\V1\BusinessInfoResource;

class ClientController extends Controller
{
    public function businessList(Request $req)
    {
        $authUser = $req->user();

        $business = Business::where('client_id', $authUser->id);
        if($req->id){
            $business = $business->where('id', $req->id);
        }
        $business = $business->latest('id')->get();

        return $this->respondOk(BusinessInfoResource::collection($business));
    }

    public function employeeList(Request $req)
    {

        $authUser = $req->user();
        $employee = Employee::where('client_id', $authUser->id);
        if(!empty($req->id)){
            $employee = $employee->where('id', $req->id);
        }
        if(!empty($req->business_id)){
            $employee = $employee->where('business_id', $req->business_id);
        }
        $employee = $employee->latest('id')->get();

        return $this->respondOk(UserInfoResource::collection($employee));
    }

    /** Validate Employee Request for add or Edit */
    public function validateEmployeeRequest(Request $req)
    {
        $req->validate([
            'business_id' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'mobile' => 'required|numeric|digits:10',
            'email' => 'nullable|string|email|max:200',
            'gender' => 'nullable|string|in:'.implode(',',gender()),
            'marital' => 'nullable|string|in:'.implode(',',marital()),
            'dob' => 'nullable|date|date_format:Y-m-d',
            'aniversary' => 'nullable|date|date_format:Y-m-d',
            'profile_image' => ''
        ]);
    }

    /** Store the New Employee Record */
    public function storeEmployee(Request $req)
    {
        $this->validateEmployeeRequest($req);
        $authUser = $req->user();
        $businessValidate = Business::where(['id' => $req->business_id,'client_id' => $authUser->id])->first();

        if(!$businessValidate){
            throw new ValidateException(['business_id' => 'Oops! this business is not listed']);
        }

        $employee = new Employee;
            $employee->business_id = $req->business_id;
            $employee->client_id = $authUser->id;
            $employee->name = $req->name;
            $employee->mobile = $req->mobile;
            $employee->email = $req->email ?? null;
            $employee->gender = $req->gender ?? null;
            $employee->marital = $req->marital ?? null;
            $employee->dob = $req->dob ?? null;
            $employee->aniversary = $req->aniversary ?? null;
            $employee->is_registered = true;
            $employee->active = true;
        $employee->save();

        return $this->respondOk(new UserInfoResource($employee));
    }

    /** Update the Employee Record */
    public function updateEmployee(Request $req, $employeeId)
    {
        $this->validateEmployeeRequest($req);
        $authUser = $req->user();
        $businessValidate = Business::where(['id' => $req->business_id,'client_id' => $authUser->id])->first();

        if(!$businessValidate){
            throw new ValidateException(['business_id' => 'Oops! this business is not listed']);
        }
        $employee = Employee::where('id', $employeeId)->where('client_id',$authUser->id)->first();
        if(!$employee){
            throw new Exception('Oops! this employee does not exist in your list');
        }
        $employee->business_id = $req->business_id;
            $employee->name = $req->name;
            $employee->mobile = $req->mobile;
            $employee->email = $req->email ?? null;
            $employee->gender = $req->gender ?? null;
            $employee->marital = $req->marital ?? null;
            $employee->dob = $req->dob ?? null;
            $employee->aniversary = $req->aniversary ?? null;
            $employee->is_registered = true;
            $employee->active = true;
        $employee->save();
        return $this->respondOk(new UserInfoResource($employee));
    }
}
