<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DefStudio\Telegraph\Models\TelegraphChat;

class Section extends Model
{
    protected $fillable = [
        'name',
        'telegraph_chat_id'
    ];

    public function chat()
    {
        return $this->belongsTo(TelegraphChat::class, 'telegraph_chat_id');

    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
