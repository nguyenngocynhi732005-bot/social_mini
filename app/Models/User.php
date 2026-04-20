<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'ID';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['ID'] ?? $this->attributes['id'] ?? null;
    }

    // Mối quan hệ bạn bè (cần kiểm tra cả 2 chiều)
    public function friendsOfMine()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id', 'ID', 'ID')
                    ->wherePivot('status', 'accepted');
    }

    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id', 'ID', 'ID')
                    ->wherePivot('status', 'accepted');
    }

    // Gom chung bạn bè (gọi $user->friends)
    public function getFriendsAttribute()
    {
        return $this->friendsOfMine->merge($this->friendOf);
    }

    // Các nhóm tham gia
    public function groups()
    {
        return $this->belongsToMany(SocialGroup::class, 'group_members', 'user_id', 'group_id', 'ID', 'id')
                    ->withPivot('role', 'joined_at');
    }

    // Danh sách bị chặn
    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id', 'ID', 'ID');
    }
}
