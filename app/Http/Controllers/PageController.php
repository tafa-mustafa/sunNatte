<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function aPropos()
    {
        return view('a-propos');
    }

    public function contact()
    {
        return view('contact');
    }

    public function mentionsLegales()
    {
        return view('mentions-legales');
    }
}
