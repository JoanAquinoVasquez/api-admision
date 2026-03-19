<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    protected $table = 'chatbot_logs';

    protected $fillable = [
        'message_user',
        'message_bot',
        'source',
        'ip_address',
        'user_identifier'
    ];
}
