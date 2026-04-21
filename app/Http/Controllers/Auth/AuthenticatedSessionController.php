<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && Schema::hasColumn('users', 'online_status')) {
            $updates = [
                'online_status' => 'Active now',
            ];

            if (Schema::hasColumn('users', 'UpdatedAt')) {
                $updates['UpdatedAt'] = now();
            }

            if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                $user->forceFill($updates)->save();
            }
        }

        return redirect()->to(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if ($user && Schema::hasColumn('users', 'online_status')) {
            $updates = [
                'online_status' => 'Offline',
            ];

            if (Schema::hasColumn('users', 'UpdatedAt')) {
                $updates['UpdatedAt'] = now();
            }

            if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                $user->forceFill($updates)->save();
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
