<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PayrollController extends Controller
{
    public function dashboard(): View
    {
        return view('payroll.dashboard');
    }

    public function employees(): View
    {
        return view('payroll.employees');
    }

    public function payTypes(): View
    {
        return view('payroll.pay-types');
    }

    public function runs(): View
    {
        return view('payroll.runs');
    }

    public function showRun(int $payrollRun): View
    {
        return view('payroll.runs.show', compact('payrollRun'));
    }

    public function payslips(): View
    {
        return view('payroll.payslips');
    }

    public function holidayPay(): View
    {
        return view('payroll.holiday-pay');
    }

    public function aMelding(): View
    {
        return view('payroll.a-melding');
    }

    public function reports(): View
    {
        return view('payroll.reports');
    }

    public function settings(): View
    {
        return view('payroll.settings');
    }
}
