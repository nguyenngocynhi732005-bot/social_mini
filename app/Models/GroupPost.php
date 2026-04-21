<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class GroupPost extends Model
{
    use HasFactory;

    protected $table = 'group_posts';

    protected $fillable = [
        'group_id',
        'social_group_id',
        'user_id',
        'content',
    ];

    private function groupColumn(): string
    {
        return Schema::hasColumn($this->getTable(), 'social_group_id') ? 'social_group_id' : 'group_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function group()
    {
        return $this->belongsTo(SocialGroup::class, $this->groupColumn());
    }
}