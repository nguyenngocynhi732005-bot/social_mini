<?php

namespace App\Http\Requests\ChatRealtime;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép mọi user đã đăng nhập gửi tin
    }

    public function rules()
    {
        return [
            'body' => 'required_without:attachments|string|nullable', // Body có thể trống nếu có file đính kèm [cite: 38]
            'type' => 'required|string', // text, image, file... [cite: 38]
        ];
    }
}
