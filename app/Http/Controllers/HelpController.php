<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        return view('help.index');
    }

    public function section(string $section)
    {
        $validSections = [
            'kom-i-gang',
            'kontakter',
            'produkter',
            'prosjekter',
            'arbeidsordrer',
            'salg',
            'okonomi',
            'lonn',
            'innboks',
            'rapporter',
            'mva',
            'innstillinger',
            'sikkerhet',
            'selskap',
        ];

        if (! in_array($section, $validSections)) {
            abort(404);
        }

        return view('help.index', ['activeSection' => $section]);
    }
}
