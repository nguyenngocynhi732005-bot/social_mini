<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'unique_id',
        'user_id',
        'Email',
        'Phone',
        'Password',
        'Name',
        'img',
        'Gender',
        'BirthDate',
        'online_status',
        'Status',
        'is_admin',
        'Reputation',
    ];

    protected $hidden = [
        'Password',
    ];

    protected $casts = [
        'BirthDate' => 'date',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
    ];

    // ✅ FIX LỖI created_at (QUAN TRỌNG)
    protected $attributes = [
        'CreatedAt' => null,
        'UpdatedAt' => null,
    ];

    // fix login dùng Password viết hoa
    public function getAuthPassword()
    {
        return $this->Password;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->unique_id)) {
                $user->unique_id = Str::uuid();
            }
        });
    }
}