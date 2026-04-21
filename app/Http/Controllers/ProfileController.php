<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    public function show($id)
    {
        return Redirect::route('newsfeed');
    }
}
