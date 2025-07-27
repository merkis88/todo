<?php

use Illuminate\Support\Facades\Route;
use DefStudio\Telegraph\Facades\Telegraph;
use App\Http\Telegraph\Handler;

Telegraph::routes()
    ->token(env('TELEGRAPH_BOT_TOKEN'))
    ->webhookHandler(Handler::class);

// Регистрируем действия, на которые ссылаются inline-кнопки
Telegraph::register('add_section_mode')->handle([Handler::class, 'add_section_mode']);
Telegraph::register('list_section')->handle([Handler::class, 'list_section']);
Telegraph::register('list_tasks')->handle([Handler::class, 'list_tasks']);
Telegraph::register('done_task')->handle([Handler::class, 'done_task']);
Telegraph::register('delete_task')->handle([Handler::class, 'delete_task']);
Telegraph::register('edit_task')->handle([Handler::class, 'edit_task']);
