<?php

namespace App\Services\ChatBot\Instagram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private string $pageToken;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->pageToken  = (string) config('services.instagram.page_token');
        $this->apiVersion = (string) config('services.instagram.api_version', 'v21.0');

        $accountId = config('services.instagram.account_id');
        $path = $accountId ? "{$accountId}/messages" : 'me/messages';
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}/{$path}";
    }

    public function sendText(string $igsid, string $text): bool
    {
        return $this->send([
            'recipient' => ['id' => $igsid],
            'message'   => ['text' => $text],
        ]);
    }

    /**
     * Quick replies — máx 13, título máx 20 chars.
     */
    public function sendQuickReplies(string $igsid, string $text, array $quickReplies): bool
    {
        return $this->send([
            'recipient' => ['id' => $igsid],
            'message'   => [
                'text'          => $text,
                'quick_replies' => $quickReplies,
            ],
        ]);
    }

    public function truncateTitle(string $title, int $max = 20): string
    {
        $title = trim($title);

        if (mb_strlen($title) <= $max) {
            return $title;
        }

        return rtrim(mb_substr($title, 0, $max - 1)) . '…';
    }

    private function send(array $payload): bool
    {
        try {
            $response = Http::withToken($this->pageToken)
                ->acceptJson()
                ->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error('Instagram API erro: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Instagram HTTP erro: ' . $e->getMessage());
            return false;
        }
    }
}
