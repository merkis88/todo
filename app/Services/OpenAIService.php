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


        $response = Http::withToken($apiKey)
            ->withOptions([
                'proxy' => $proxy,
                'timeout' => 20,

            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3-5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Ты дружелюбный Telegram-бот для помощи с задачами.'],
                    ['role' => 'user',   'content' => $message],
                ],
            ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];

        }

        Log::error('GPT Request Failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return 'Произошла ошибка при обращении к GPT 🧨';
    }
}

