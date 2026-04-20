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

    protected $keyType = 'int';

    public $incrementing = true;

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
        'birth_date' => 'date',
    ];

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
}
