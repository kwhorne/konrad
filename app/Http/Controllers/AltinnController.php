<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AltinnController extends Controller
{
    /**
     * Display the Altinn dashboard with deadlines and submissions.
     */
    public function index(): View
    {
        return view('altinn.index');
    }
}
