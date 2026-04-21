<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    const UPDATED_AT = null;

    protected $fillable = ['receiver_id', 'sender_id', 'type', 'is_read', 'post_id'];

    public function sender() {
        return $this->belongsTo(User::class, 'sender_id', 'ID'); 
    }
}