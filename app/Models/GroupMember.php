<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'group_members';

    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'social_group_id',
        'user_id',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function group()
    {
        $groupColumn = Schema::hasColumn($this->getTable(), 'social_group_id') ? 'social_group_id' : 'group_id';

        return $this->belongsTo(SocialGroup::class, $groupColumn);
    }
}
