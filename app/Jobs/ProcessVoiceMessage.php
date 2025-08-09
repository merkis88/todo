<?php

namespace App\Jobs;

use App\Models\Section;
use App\Services\DeepSeekService;
use App\Services\Speech\SpeechToTextService;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessVoiceMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $fileID;
    protected int $chatID;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileID, int $chatID)
    {
        $this->fileID = $fileID;
        $this->chatID = $chatID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chat = TelegraphChat::find($this->chatID);

        if (!$chat) {
            return;
        }

        $fileName = "voice_{$this->fileID}.ogg";
        $localPath = storage_path("app/voices/" . $fileName);

        Telegraph::store($this->fileID, $localPath);

        $text = "Отвечай на том языке, на котором к тебе приходит запрос: " . app(SpeechToTextService::class)->handle($localPath);

        // Отправляет в DeepSeek
        app(DeepSeekService::class)->handle($text, $chat);
    }
}
