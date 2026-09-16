<?php

namespace App\Services\ChatBot\Instagram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private string $pageToken;
    private string $apiVersion;
    private string $baseUrl;
    private ?string $accountId;

    public function __construct()
    {
        $this->pageToken  = (string) config('services.instagram.page_token');
        $this->apiVersion = (string) config('services.instagram.api_version', 'v21.0');
        $this->accountId  = config('services.instagram.account_id') ?: null;

        $path = $this->accountId ? "{$this->accountId}/messages" : 'me/messages';
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}/{$path}";

        Log::info('InstagramService init', [
            'api_version' => $this->apiVersion,
            'account_id' => $this->accountId ?: '(vazio → usa me/messages)',
            'base_url' => $this->baseUrl,
            'page_token_set' => $this->pageToken !== '',
            'page_token_prefix' => $this->pageToken !== ''
                ? substr($this->pageToken, 0, 8) . '...'
                : '(vazio)',
        ]);
    }

    public function sendText(string $igsid, string $text): bool
    {
        Log::info('Instagram sendText', [
            'to' => $igsid,
            'text_preview' => mb_substr($text, 0, 80),
            'text_length' => mb_strlen($text),
        ]);

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
        Log::info('Instagram sendQuickReplies', [
            'to' => $igsid,
            'text_preview' => mb_substr($text, 0, 80),
            'quick_replies_count' => count($quickReplies),
            'quick_replies' => $quickReplies,
        ]);

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
        if ($this->pageToken === '') {
            Log::error('Instagram API: INSTAGRAM_PAGE_TOKEN está vazio no .env');
            return false;
        }

        try {
            Log::info('Instagram API request', [
                'url' => $this->baseUrl,
                'payload' => $payload,
            ]);

            $response = Http::withToken($this->pageToken)
                ->acceptJson()
                ->post($this->baseUrl, $payload);

            Log::info('Instagram API response', [
                'status' => $response->status(),
                'ok' => $response->successful(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('Instagram API erro', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $this->baseUrl,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Instagram HTTP exception', [
                'message' => $e->getMessage(),
                'url' => $this->baseUrl,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
