<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    public $timestamps = false;

    protected $fillable = [
        'ID',
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
        'First_name',
        'Last_name',
        'Name',
        'Email',
        'Phone',
        'Password',
        'BirthDate',
        'AvatarURL',
        'img',
        'Gender',
        'online_status',
        'Status',
        'is_admin',
        'Reputation',
        'unique_id',
        'user_id',
        'CreatedAt',
        'UpdatedAt',
    ];

    protected $hidden = [
        'password',
        'Password',
        'remember_token',
    ];

    protected $casts = [
        'BirthDate' => 'date',
        'birth_date' => 'date',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $user) {
            if (empty($user->unique_id)) {
                $user->unique_id = ((int) self::query()->max('unique_id')) + 1;
            }

            if (empty($user->user_id)) {
                $user->user_id = ((int) self::query()->max('user_id')) + 1;
            }
        });
    }

    public function getIdAttribute()
    {
        return $this->attributes['ID'] ?? $this->attributes['id'] ?? null;
    }

    public function getAuthPassword()
    {
        return (string) ($this->Password ?? $this->password ?? '');
    }

    public function getNameAttribute()
    {
        if (!empty($this->attributes['name'])) {
            return (string) $this->attributes['name'];
        }

        if (!empty($this->attributes['Name'])) {
            return (string) $this->attributes['Name'];
        }

        $first = trim((string) ($this->attributes['First_name'] ?? $this->attributes['first_name'] ?? ''));
        $last = trim((string) ($this->attributes['Last_name'] ?? $this->attributes['last_name'] ?? ''));
        $combined = trim($first . ' ' . $last);

        if ($combined !== '') {
            return $combined;
        }

        $email = $this->attributes['Email'] ?? $this->attributes['email'] ?? '';
        if ($email !== '') {
            return (string) $email;
        }

        return 'User #' . ($this->attributes['ID'] ?? $this->attributes['id'] ?? 'unknown');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['Name'] = trim((string) $value);
    }

    public function getEmailAttribute()
    {
        return (string) ($this->attributes['Email'] ?? $this->attributes['email'] ?? '');
    }

    public function setEmailAttribute($value)
    {
        $email = trim((string) $value);

        if ($email === '' && $this->exists) {
            return;
        }

        $this->attributes['Email'] = $email;
        $this->attributes['email'] = $email;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        if (!empty($this->AvatarURL)) {
            return (string) $this->AvatarURL;
        }

        if (!empty($this->avatar_path)) {
            return asset('storage/' . ltrim($this->avatar_path, '/'));
        }

        if (!empty($this->img)) {
            return asset('storage/' . ltrim($this->img, '/'));
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

    public function conversations()
    {
        return $this->belongsToMany(
            Conversation::class,
            'conversation_participants',
            'user_id',
            'conversation_id',
            'ID',
            'id'
        );
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id', 'ID');
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
