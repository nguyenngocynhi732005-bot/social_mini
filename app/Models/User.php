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

    // The current schema uses custom timestamp columns; disable Laravel defaults.
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'name',
        'email',
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
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
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

    public function getAuthPassword()
    {
        return (string) ($this->Password ?? $this->password ?? '');
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

    public function getNameAttribute()
    {
        if (!empty($this->attributes['name'])) {
            return (string) $this->attributes['name'];
        }

        if (!empty($this->attributes['Name'])) {
            return (string) $this->attributes['Name'];
        }

        $first = trim((string) ($this->attributes['First_name'] ?? ''));
        $last = trim((string) ($this->attributes['Last_name'] ?? ''));
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

    public function getEmailAttribute()
    {
        return (string) ($this->attributes['email'] ?? $this->attributes['Email'] ?? '');
    }

    public function setEmailAttribute($value)
    {
        $email = trim((string) $value);

        if ($email === '' && $this->exists) {
            return;
        }

        $this->attributes['Email'] = $email;
    }
}
