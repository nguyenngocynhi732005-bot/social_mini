<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FriendshipController extends Controller
{
    public function search(Request $request)
    {
        return Redirect::route('newsfeed');
    }
}
