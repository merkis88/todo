<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use DefStudio\Telegraph\Models\TelegraphBot;
use App\Http\Telegraph\Handler;

Route::telegraph()
    ->token('7842446740:AAEJYeYBQZhEShnMKpPFqvHmYeLy9V9IBaw')
    ->webhookHandler(Handler::class);
