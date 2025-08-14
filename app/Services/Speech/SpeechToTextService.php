<?php

namespace App\Services\Speech;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpeechToTextService
{
    public function recognize(string $oggPath): string
    {
        $wavPath = str_replace('.ogg', '.wav', $oggPath);
        $command = "ffmpeg -y -i {$oggPath} -acodec pcm_s16le -ar 16000 -ac 1 {$wavPath}";

        // 2>&1 перенаправляет вывод ошибок (stderr) в стандартный вывод (stdout) для логирования
        exec($command . " 2>&1", $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($wavPath)) {
            Log::error('FFMPEG conversion failed.', ['output' => $output]);
            throw new \Exception('Ошибка конвертации аудиофайла с помощью FFMPEG.');
        }

        $apiKey = config('services.speechkit.key');
        if (empty($apiKey)) {
            throw new \Exception('API ключ для Yandex SpeechKit не настроен в config/services.php');
        }

        $url = 'https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&format=lpcm&sampleRateHertz=16000';

        $response = Http::timeout(45)
            ->withHeaders(['Authorization' => 'Api-Key ' . $apiKey])
            ->withBody(file_get_contents($wavPath), 'audio/x-pcm')
            ->post($url);

        if (!$response->ok()) {
            Log::error('Yandex SpeechKit Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Ошибка API распознавания речи Yandex.');
        }

        return $response->json('result') ?? '';
    }
}
