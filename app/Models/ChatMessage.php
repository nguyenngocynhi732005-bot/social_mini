<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'type'];

    /**
     * Mỗi tin nhắn phải thuộc về một cuộc hội thoại cụ thể [cite: 28, 29]
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    /**
     * Mỗi tin nhắn được gửi bởi một User (Người dùng) [cite: 28, 29]
     */
    public function sender()
    {
        // Explicitly specify the owner key since User model uses 'ID' as primary key
        return $this->belongsTo(User::class, 'sender_id', 'ID');
    }

    /**
     * Một tin nhắn có thể có nhiều file đính kèm [cite: 30, 31]
     */
    public function attachments()
    {
        return $this->hasMany(ChatAttachment::class);
    }
}
