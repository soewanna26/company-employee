<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $totalCompanies = Company::count();
        $totalEmployees = Employee::count();
        $recentCompanies = Company::latest()->take(5)->get();

        return view('dashboard', compact('totalCompanies', 'totalEmployees', 'recentCompanies'));
    }
}
