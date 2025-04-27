<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    public function ask(string $message): string
    {
        $apiKey = config('services.deepseek.key');
        $baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com/v1');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? '⚠️ Пустой ответ от DeepSeek';
            }

            Log::error('DeepSeek Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return '🚫 Ошибка при обращении к DeepSeek';
        } catch (\Throwable $e) {
            Log::error('DeepSeek Fatal Exception', ['message' => $e->getMessage()]);
            return '💥 Ошибка соединения с DeepSeek';
        }
    }
}
