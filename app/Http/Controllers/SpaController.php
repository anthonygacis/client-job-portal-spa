<?php

namespace App\Http\Controllers;

class SpaController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function appIndex()
    {
        return view('index');
    }
}
