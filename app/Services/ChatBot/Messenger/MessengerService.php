<?php

namespace App\Services\ChatBot\Messenger;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessengerService
{
    /**
     * Create a new class instance.
     */

    private string $pageToken;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->pageToken  = config('services.messenger.page_token');
        $this->apiVersion = config('services.messenger.api_version', 'v21.0');
        $this->baseUrl    = "https://graph.facebook.com/{$this->apiVersion}/me/messages";
    }

    // ─────────────────────────────────────────────
    // Texto simples
    // ─────────────────────────────────────────────
    public function sendText(string $psid, string $text): bool
    {
        return $this->send([
            'recipient' => ['id' => $psid],
            'messaging_type' => 'RESPONSE',
            'message'   => ['text' => $text],
        ]);
    }
 
    // ─────────────────────────────────────────────
    // Quick Replies (botões de resposta rápida)
    // Máximo 13 opções, título máx 20 chars
    // ─────────────────────────────────────────────
    public function sendQuickReplies(string $psid, string $text, array $quickReplies): bool
    {
        return $this->send([
            'recipient' => ['id' => $psid],
            'messaging_type' => 'RESPONSE',
            'message'   => [
                'text'          => $text,
                'quick_replies' => $quickReplies,
            ],
        ]);
    }
 
    // ─────────────────────────────────────────────
    // Botões genéricos (template button) — máx 3
    // ─────────────────────────────────────────────
    public function sendButtons(string $psid, string $text, array $buttons): bool
    {
        return $this->send([
            'recipient' => ['id' => $psid],
            'messaging_type' => 'RESPONSE',
            'message'   => [
                'attachment' => [
                    'type'    => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text'          => $text,
                        'buttons'       => $buttons, // array de ['type'=>'postback','title'=>'...','payload'=>'...']
                    ],
                ],
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // Generic template (carrossel) — equivalente à lista do WhatsApp
    // Máximo 10 elementos; cada um pode ter até 3 botões
    // ─────────────────────────────────────────────
    public function sendGenericTemplate(string $psid, array $elements): bool
    {
        return $this->send([
            'recipient' => ['id' => $psid],
            'messaging_type' => 'RESPONSE',
            'message'   => [
                'attachment' => [
                    'type'    => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements'      => $elements,
                    ],
                ],
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
 
    // ─────────────────────────────────────────────
    // Marcar mensagem como lida (opcional / UX)
    // ─────────────────────────────────────────────
    public function markSeen(string $psid): bool
    {
        return $this->send([
            'recipient'     => ['id' => $psid],
            'sender_action' => 'mark_seen',
        ]);
    }
 
    // ─────────────────────────────────────────────
    // Mostrar "a escrever..." (opcional / UX)
    // ─────────────────────────────────────────────
    public function typingOn(string $psid): bool
    {
        return $this->send([
            'recipient'     => ['id' => $psid],
            'sender_action' => 'typing_on',
        ]);
    }

    public function typingOff(string $psid): bool
    {
        return $this->send([
            'recipient'     => ['id' => $psid],
            'sender_action' => 'typing_off',
        ]);
    }
 
    // ─────────────────────────────────────────────
    // Chamada base à Graph API
    // ─────────────────────────────────────────────
    private function send(array $payload): bool
    {
        try {
            $response = Http::withToken($this->pageToken)
                ->post($this->baseUrl, $payload);
 
            if ($response->failed()) {
                Log::error('Messenger API erro: ' . $response->body());
                return false;
            }
 
            return true;
        } catch (\Throwable $e) {
            Log::error('Messenger HTTP erro: ' . $e->getMessage());
            return false;
        }
    }

}


