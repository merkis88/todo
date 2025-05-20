<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'is_done', 'telegraph_chat_id', 'remind_at'];

    public function chat()
    {
        return $this->belongsTo(\DefStudio\Telegraph\Models\TelegraphChat::class, 'telegraph_chat_id');
    }
}
