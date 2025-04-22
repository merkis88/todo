<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OpenAIService
{
    public function ask(string $message): string
    {
        $apiKey = config('services.openai.key');
        $proxy = config('services.openai.proxy');

        try {
            $response = Http::withOptions([
                'timeout' => 20,
            ])->post('https://gpt-proxy-lzav.onrender.com/gpt', [
                'model' => 'openai/gpt-3.5-turbo', 
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? '⚠️ Ответ GPT пустой';
            }

            Log::error('GPT Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Произошла ошибка при обращении к GPT 🧨';
        } catch (\Throwable $e) {
            Log::error('GPT Fatal Exception', ['message' => $e->getMessage()]);
            return 'Ошибка соединения с GPT 🧨';
        }
    }
}
