<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPost extends Model
{
    use HasFactory;

    protected $table = 'group_posts';

    protected $fillable = [
        'social_group_id',
        'user_id',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function group()
    {
        return $this->belongsTo(SocialGroup::class, 'social_group_id');
    }
}