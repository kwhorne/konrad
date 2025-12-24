<?php

namespace App\Http\Controllers;

class WorkOrderController extends Controller
{
    public function index()
    {
        return view('work-orders.index');
    }

    public function create()
    {
        return redirect()->route('work-orders.index');
    }

    public function show()
    {
        return redirect()->route('work-orders.index');
    }

    public function edit()
    {
        return redirect()->route('work-orders.index');
    }
}
