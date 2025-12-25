<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('welcome', ['openModal' => 'privacy']);
    }

    public function terms(): View
    {
        return view('welcome', ['openModal' => 'terms']);
    }
}
