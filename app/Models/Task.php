<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DefStudio\Telegraph\Models\TelegraphChat;

class Task extends Model
{
    protected $fillable = [
        'title',
        'is_done',
        'telegraph_chat_id',
        'remind_at',
        'section_id',

    ];

    public function chat()
    {
        return $this->belongsTo(TelegraphChat::class, 'telegraph_chat_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
