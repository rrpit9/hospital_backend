<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function adminDashboard(Request $req)
    {
        return view('home');
    }

    public function clientDashboard(Request $req)
    {
        return view('home');
    }

    public function employeeDashboard(Request $req)
    {
        return view('home');
    }

    public function customerDashboard(Request $req)
    {
        return view('home');
    }
}
