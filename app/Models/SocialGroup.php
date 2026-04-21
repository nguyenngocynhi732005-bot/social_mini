<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SocialGroup extends Model
{
    use HasFactory;

    protected $table = 'social_groups';

    protected $fillable = [
        'name',
        'description',
        'privacy',
        'avatar_image',
        'cover_image',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'ID');
    }

    public function members()
    {
        $groupColumn = Schema::hasColumn('group_members', 'social_group_id') ? 'social_group_id' : 'group_id';

        return $this->hasMany(GroupMember::class, $groupColumn);
    }
}
