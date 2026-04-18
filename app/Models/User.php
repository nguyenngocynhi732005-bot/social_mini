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

    // Use custom primary key name from actual DB schema
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';

    // Table name
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ID',
        'First_name',
        'Last_name',
        'Email',
        'Password',
        'BirthDate',
        'AvatarURL',
        'Gender',
        'online_status',
        'Status',
        'is_admin',
        'Reputation',
        'Phone',
        'img',
        'unique_id',
        'user_id',
        'CreatedAt',
        'UpdatedAt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'Password',
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

    /**
     * Các cuộc trò chuyện mà user này tham gia.
     */
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

    /**
     * Các tin nhắn do user này gửi.
     */
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id', 'ID');
    }

    /**
     * Disable timestamps since the table uses CreatedAt/UpdatedAt
     */
    public $timestamps = false;

    /**
     * Get the user's display name
     * Combines First_name and Last_name from custom schema
     */
    public function getNameAttribute()
    {
        // Try standard name column first
        if (isset($this->attributes['name']) && $this->attributes['name'] !== '') {
            return $this->attributes['name'];
        }

        // Try custom columns
        $first = trim((string) ($this->attributes['First_name'] ?? ''));
        $last = trim((string) ($this->attributes['Last_name'] ?? ''));
        $combined = trim($first . ' ' . $last);

        if ($combined !== '') {
            return $combined;
        }

        // Fallback to email
        $email = $this->attributes['Email'] ?? $this->attributes['email'] ?? '';
        if ($email !== '') {
            return $email;
        }

        return 'User #' . ($this->attributes['ID'] ?? $this->attributes['id'] ?? 'unknown');
    }

    /**
     * Get the user's email (handles both standard and custom column names)
     */
    public function getEmailAttribute()
    {
        return $this->attributes['email'] ?? $this->attributes['Email'] ?? '';
    }
}
