<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $nameColumn = Schema::hasColumn('users', 'Name') ? 'Name' : 'name';
        $emailColumn = Schema::hasColumn('users', 'Email') ? 'Email' : 'email';
        $passwordColumn = Schema::hasColumn('users', 'Password') ? 'Password' : 'password';

        $request->validate([
            'Name' => ['required', 'string', 'max:255'],
            'Email' => ['required', 'email', 'unique:users,' . $emailColumn],
            'Phone' => ['nullable', 'string', 'max:10'],
            'Password' => ['required', 'confirmed', 'min:6'],
            'BirthDate' => ['required', 'date'],
            'Gender' => ['nullable', 'string'],
        ]);

        $fullName = trim((string) $request->input('Name'));
        $nameParts = preg_split('/\s+/', $fullName, 2) ?: [];
        $firstName = (string) ($nameParts[0] ?? $fullName);
        $lastName = (string) ($nameParts[1] ?? ($nameParts[0] ?? $fullName));

        $rawGender = (string) $request->input('Gender', 'Tùy chỉnh');
        $genderMap = [
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Tùy chỉnh',
            'Nam' => 'Nam',
            'Nữ' => 'Nữ',
            'Tùy chỉnh' => 'Tùy chỉnh',
        ];
        $genderValue = $genderMap[$rawGender] ?? 'Tùy chỉnh';

        $nextUniqueId = ((int) User::query()->max('unique_id')) + 1;
        $nextUserId = ((int) User::query()->max('user_id')) + 1;

        $payload = [
            'unique_id' => $nextUniqueId,
            'user_id' => $nextUserId,
            $nameColumn => (string) $request->input('Name'),
            $emailColumn => (string) $request->input('Email'),
            $passwordColumn => Hash::make((string) $request->input('Password')),
            'online_status' => 'Offline',
            'is_admin' => 0,
            'Reputation' => 100,
        ];

        if (Schema::hasColumn('users', 'First_name')) {
            $payload['First_name'] = $firstName;
        }

        if (Schema::hasColumn('users', 'Last_name')) {
            $payload['Last_name'] = $lastName;
        }

        if (Schema::hasColumn('users', 'Phone')) {
            $payload['Phone'] = $request->input('Phone');
        }

        if (Schema::hasColumn('users', 'BirthDate')) {
            $payload['BirthDate'] = $request->input('BirthDate');
        }

        if (Schema::hasColumn('users', 'Gender')) {
            $payload['Gender'] = $genderValue;
        }

        if (Schema::hasColumn('users', 'img')) {
            $payload['img'] = null;
        }

        if (Schema::hasColumn('users', 'Status')) {
            $payload['Status'] = 1;
        }

        DB::table('users')->insert($payload);

        $keyName = (new User())->getKeyName();
        $user = User::query()
            ->where($emailColumn, (string) $request->input('Email'))
            ->orderByDesc($keyName)
            ->first();

        if (!$user) {
            $user = User::query()->orderByDesc($keyName)->first();
        }

        if ($user) {
            event(new Registered($user));
        }

        return redirect()->route('login')->with('status', 'Đăng ký thành công. Vui lòng đăng nhập để tiếp tục.');
    }
}
