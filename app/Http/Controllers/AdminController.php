<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    public function index()
    {
        return Redirect::route('newsfeed');
    }
}
