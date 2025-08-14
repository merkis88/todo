<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::get('/download-tasks/{chatId}', function ($chatId) {
    $file = "exports/tasks_{$chatId}.json";

    if (!Storage::disk('local')->exists($file)) {
        abort(404, 'Файл не найден');
    }

    return Response::download(storage_path("app/{$file}"), "tasks_{$chatId}.json");
});


use App\Jobs\TestJob;

Route::get('/test-queue', function () {
    TestJob::dispatch();
    return '>>> Тестовая задача успешно отправлена в очередь! <<<';
});
