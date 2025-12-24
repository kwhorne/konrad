<?php

namespace App\Http\Controllers;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index');
    }

    public function create()
    {
        return redirect()->route('projects.index');
    }

    public function show()
    {
        return redirect()->route('projects.index');
    }

    public function edit()
    {
        return redirect()->route('projects.index');
    }
}
