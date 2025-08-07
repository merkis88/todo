<?php

namespace App\Services\Speech;

use Illuminate\Support\Facades\Http;

class SpeechToTextService
{
    /**
     * Обрабатывает .ogg файл: конвертирует и отправляет в Yandex
     */
    public function handle(string $oggPath): string
    {
        $wavPath = str_replace('.ogg', '.wav', $oggPath);

        // Конвертируем через ffmpeg
        exec("ffmpeg -y -i {$oggPath} -ar 16000 -ac 1 -f wav {$wavPath}");

        // Отправляем .wav в Yandex SpeechKit
        $response = Http::withHeaders([
            'Authorization' => 'Api-Key ' . config('services.speechkit.key'),
            'Content-Type' => 'audio/wav',
        ])
            ->withBody(file_get_contents($wavPath), 'audio/wav')
            ->post('https://stt.api.cloud.yandex.net/speech/v1/stt:recognize');

        if (!$response->ok()) {
            throw new \Exception('SpeechKit error: ' . $response->body());
        }

        return $response->json('result') ?? 'Не удалось распознать голос.';
    }
}
