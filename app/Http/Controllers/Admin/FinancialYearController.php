<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialYearRequest;
use App\Models\FinancialYear;
use App\Repositories\FinancialYearRepository;
use Illuminate\Http\Request;

class FinancialYearController extends Controller
{
    public function index()
    {
        $financialYears = FinancialYear::paginate(10);
        return view('admin/financial-years/index',compact('financialYears'));
    }

//    public function store(FinancialYearRequest $request)
//    {
//        FinancialYearRepository::financialAddorUpdate($request);
//        return back()->withSuccess(__('Financial year created successfully'));
//    }
}
