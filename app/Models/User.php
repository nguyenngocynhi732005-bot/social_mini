<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $incrementing = true;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'email',
        'first_name',
        'last_name',
        'phone',
        'birth_date',
        'gender',
        'avatar',
        'avatar_path',
        'cover_image',
        'cover_path',
        'bio',
        'work',
        'education',
        'location',
        'hometown',
        'relationship',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['ID'] ?? $this->attributes['id'] ?? null;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        if (!empty($this->avatar_path)) {
            return asset('storage/' . ltrim($this->avatar_path, '/'));
        }

        return 'https://i.pravatar.cc/160?u=' . urlencode((string) $this->id);
    }

    public function getCoverUrlAttribute(): string
    {
        if (!empty($this->cover_image)) {
            return asset('storage/' . ltrim($this->cover_image, '/'));
        }

        if (!empty($this->cover_path)) {
            return asset('storage/' . ltrim($this->cover_path, '/'));
        }

        return '';
    }

    public function getLocationAttribute($value)
    {
        return $value ?? $this->attributes['living_city'] ?? null;
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = $value;
    }

    public function getRelationshipAttribute($value)
    {
        return $value ?? $this->attributes['relationship_status'] ?? null;
    }

    public function setRelationshipAttribute($value)
    {
        $this->attributes['relationship'] = $value;
    }

    public function friendsOfMine()
    {
        return $this->belongsToMany(self::class, 'friendships', 'user_id', 'friend_id', 'ID', 'ID')
            ->wherePivot('status', 'accepted');
    }

    public function friendOf()
    {
        return $this->belongsToMany(self::class, 'friendships', 'friend_id', 'user_id', 'ID', 'ID')
            ->wherePivot('status', 'accepted');
    }

    public function getFriendsAttribute()
    {
        return $this->friendsOfMine->merge($this->friendOf);
    }

    public function groups()
    {
        return $this->belongsToMany(SocialGroup::class, 'group_members', 'user_id', 'group_id', 'ID', 'id')
            ->withPivot('role', 'joined_at');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(self::class, 'user_blocks', 'blocker_id', 'blocked_id', 'ID', 'ID');
    }
}
