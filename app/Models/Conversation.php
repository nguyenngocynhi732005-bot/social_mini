<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConversationParticipant;
use App\Models\ChatMessage;

class Conversation extends Model
{
    use HasFactory;

    // Cho phép lưu các cột này vào database [cite: 24, 25]
    protected $fillable = ['label', 'type', 'chat_background_path'];

    /**
     * Một cuộc hội thoại có nhiều thành viên tham gia [cite: 26, 27]
     */
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Các user tham gia cuộc trò chuyện này.
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'conversation_participants',
            'conversation_id',
            'user_id',
            'id',
            'ID'
        );
    }

    /**
     * Một cuộc hội thoại có nhiều tin nhắn [cite: 28, 29]
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
