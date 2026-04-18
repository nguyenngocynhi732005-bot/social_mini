<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Name' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'email', 'unique:users,Email'],
            'Phone' => ['nullable'],
            'Password' => ['required', 'confirmed', 'min:6'],
            'BirthDate' => ['required', 'date'],
            'Gender' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'unique_id' => Str::uuid(),
            'user_id' => rand(10000,99999),

            'Name' => $request->Name,
            'Email' => $request->Email,
            'Phone' => $request->Phone,
            'Password' => Hash::make($request->Password),

            'BirthDate' => $request->BirthDate,
            'Gender' => $request->Gender,

            'img' => null,

            'online_status' => 0,
            'Status' => 1,
            'is_admin' => 0,
            'Reputation' => 0,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}