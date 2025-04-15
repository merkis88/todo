<?php

namespace App\Http\Telegraph;

use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Log;
use DefStudio\Telegraph\Handlers\WebhookHandler;

class Handler extends WebhookHandler
{
    public function handleStart(Stringable $message): void
{
    $this->chat->message("Привет! Я успешно запущен")->send();
}


    public function handleUnknownCommand(Stringable $message): void
    {
        $this->chat->message("Привет,я получил твоё сообщение: $message")->send();
    }
}
