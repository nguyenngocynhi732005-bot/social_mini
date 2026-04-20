<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialGroup extends Model
{
    use HasFactory;

    protected $table = 'social_groups';

    protected $fillable = [
        'name',
        'description',
        'privacy',
        'is_hidden',
        'cover_image',
        'avatar_image',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'ID');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class, 'social_group_id');
    }
}
