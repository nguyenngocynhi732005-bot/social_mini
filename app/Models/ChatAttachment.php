<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChatAttachment extends Model
{
    // Cho phép lưu các thông tin file [cite: 57, 58]
    protected $fillable = [
        'chat_message_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size'
    ];

    protected $appends = ['file_url'];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}
