<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $table = 'friendships';
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
        'action_user_id',
    ];

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    /**
     * Get the first user in the friendship
     */
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    /**
     * Get the second user in the friendship
     */
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'friend_id', 'ID');
    }

    /**
     * Get the user who took the action
     */
    public function actionUser()
    {
        return $this->belongsTo(User::class, 'action_user_id', 'ID');
    }

    /**
     * Get the other user in the friendship
     */
    public function getOtherUser($userId)
    {
        if ($this->user_id == $userId) {
            return $this->userTwo;
        }
        return $this->userOne;
    }
}
