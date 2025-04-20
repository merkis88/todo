<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    public function ask(string $message): string
    {
        $apiKey = config('services.openai.key');


        $response = Http::withToken($apiKey)
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

        return 'Произошла ошибка при обращении к GPT 🧨';
    }
}

