<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DefStudio\Telegraph\Models\TelegraphChat as BaseTelegraphChat;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegraphChat extends BaseTelegraphChat
{
    use HasFactory;

    // Связь с задачами
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Связь с разделами
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
