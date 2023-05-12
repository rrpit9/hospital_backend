<?php

namespace App\Http\Controllers\Api\Admin\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function upgradeClientBusinessPlan(Request $req)
    {
        $req->validate([
            'plan_id' => 'required|numeric|min:1',
            'business_id' => 'required|numeric|min:1'
        ]);
        
    }
}
