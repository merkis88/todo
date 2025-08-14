<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    public function ask(string $userMessage, string $systemMessage = null): string
    {
        $apiKey = config('services.deepseek.key');
        $baseUrl = config('services.deepseek.base_url');

        if (empty($apiKey) || empty($baseUrl)) {
            throw new \Exception('API ключ или URL для DeepSeek не настроены в config/services.php');
        }

        $messages = [];
        if ($systemMessage) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60) // Таймаут на запрос
                ->post($baseUrl . '/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Извините, я не смог получить ответ.';
            }

            Log::error('DeepSeek Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Ошибка при обращении к DeepSeek.';

        } catch (\Throwable $e) {
            Log::error('DeepSeek Connection Exception', ['message' => $e->getMessage()]);
            throw new \Exception('Критическая ошибка соединения с сервисом DeepSeek.');
        }
    }
}
