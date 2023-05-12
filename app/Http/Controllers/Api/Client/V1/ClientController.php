<?php

namespace App\Http\Controllers\Api\Client\V1;

use Exception;
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

    /** Validate Business Request for Create and Update */
    public function validateBusinessRequest(Request $req)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:200',
            'mobile' => 'required|numeric|digits:10',
            'address' => 'required|string|max:255',
            'pincode' => 'required|numeric|digits:6',
            'active' => 'required|boolean',
            'logo' => 'nullable'
        ];
        $req->validate($rules);
    }

    /** Store the New Business Record */
    public function storeBusiness(Request $req)
    {
        $this->validateBusinessRequest($req);
        $authUser = $req->user();

        $business = new Business;
            $business->client_id = $authUser->id;
            $business->name = $req->name;
            $business->email = $req->email ?? null;
            $business->mobile = $req->mobile ?? null;
            $business->address = $req->address ?? null;
            $business->pincode = $req->pincode ?? null;
            $business->valid_till = now();
            $business->active = $req->active;
        $business->save();

        return $this->respondOk(new BusinessInfoResource($business));
    }

    /** Update the Business Record */
    public function updateBusiness(Request $req, $businessId)
    {
        $this->validateBusinessRequest($req);
        $authUser = $req->user();

        $business = Business::where('id', $businessId)->where('client_id', $authUser->id)->first();
        if(!$business){
            throw new ValidateException(['name' => 'Oops! this business is not listed']);
        }
        $business->name = $req->name;
            $business->email = $req->email ?? null;
            $business->mobile = $req->mobile ?? null;
            $business->address = $req->address ?? null;
            $business->pincode = $req->pincode ?? null;
            $business->active = $req->active;
        $business->save();

        return $this->respondOk(new BusinessInfoResource($business));
    }

    /** Delete the Business Record */
    public function deleteBusiness(Request $req, $businessId)
    {
        $authUser = $req->user();
        $business = Business::where('id', $businessId)->where('client_id', $authUser->id)->first();
        if(!$business){
            throw new Exception('Oops! this business does not exist in your list');
        }
        $business->delete();
        return $this->respondOk('Business Deleted Success');
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

    /** Validate Employee Request for Create and Update */
    public function validateEmployeeRequest(Request $req, $formType = 'create', $employeeId = 0)
    {
        $rules = [
            'business_id' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:'.implode(',',gender()),
            'marital' => 'nullable|string|in:'.implode(',',marital()),
            'dob' => 'nullable|date|date_format:Y-m-d',
            'aniversary' => 'nullable|date|date_format:Y-m-d',
            'active' => 'required|boolean',
            'profile_image' => 'nullable'
        ];
        if($formType == 'create'){
            $rules['mobile'] = 'required|numeric|digits:10|unique:employees';
            $rules['email'] = 'nullable|string|email|max:200|unique:employees';
        }else{
            $rules['mobile'] = 'required|numeric|digits:10|unique:employees,mobile,'.$employeeId;
            $rules['email'] = 'nullable|string|email|max:200|unique:employees,email,'.$employeeId;
        }
        $req->validate($rules);
    }

    /** Store the New Employee Record */
    public function storeEmployee(Request $req)
    {
        $this->validateEmployeeRequest($req, 'create');
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
            $employee->active = $req->active;
        $employee->save();

        return $this->respondOk(new UserInfoResource($employee));
    }

    /** Update the Employee Record */
    public function updateEmployee(Request $req, $employeeId)
    {
        $this->validateEmployeeRequest($req, 'update', $employeeId);
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
            $employee->active = $req->active;
        $employee->save();
        return $this->respondOk(new UserInfoResource($employee));
    }

    /** Delete the Employee Record */
    public function deleteEmployee(Request $req, $employeeId)
    {
        $authUser = $req->user();
        $employee = Employee::where('id', $employeeId)->where('client_id',$authUser->id)->first();
        if(!$employee){
            throw new Exception('Oops! this employee does not exist in your list');
        }
        $employee->delete();
        return $this->respondOk('Employee Deleted Success');
    }
}
