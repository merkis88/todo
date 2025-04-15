<?php
namespace App\Http\Telegraph;

use DefStudio\Telegraph\Handlers\WebhookHandler;

class Handler extends WebhookHandler {

    public function hello(): void
    {
        $this->reply("Дарова");
    }
}
