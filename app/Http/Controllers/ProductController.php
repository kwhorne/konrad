<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function create()
    {
        return redirect()->route('products.index');
    }

    public function show()
    {
        return redirect()->route('products.index');
    }

    public function edit()
    {
        return redirect()->route('products.index');
    }
}
