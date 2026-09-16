<?php

namespace App\Http\Controllers\Api\ChatBot\Messenger;

use App\Http\Controllers\Controller;
use App\Models\ChatBot\OptionBot;
use App\Models\ChatBot\QuestionBot;
use App\Models\Student\StudentUcm;
use App\Services\ChatBot\Messenger\MessengerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MessengerBotController extends Controller
{
    //
    
    public function __construct(protected MessengerService $messenger) {}
 
    // ─────────────────────────────────────────────
    // GET /webhook/messenger  →  verificação Meta
    // ─────────────────────────────────────────────
    public function verify(Request $request)
    {
        $verifyToken = config('services.messenger.verify_token');
 
        if (
            $request->hub_mode === 'subscribe' &&
            $request->hub_verify_token === $verifyToken
        ) {
            return response($request->hub_challenge, 200);
        }
 
        return response('Erro de verificação', 403);
    }
 
    // ─────────────────────────────────────────────
    // POST /webhook/messenger  →  eventos recebidos
    // ─────────────────────────────────────────────
    public function handle(Request $request)
    {
        // DEBUG: o que o Facebook envia (ver storage/logs/laravel.log)
        Log::info('Messenger webhook RAW', [
            'raw_body' => $request->getContent(),
            'json' => $request->all(),
        ]);

        // 1. Valida assinatura HMAC
        $signature = $request->header('X-Hub-Signature-256');
        $expected  = 'sha256=' . hash_hmac(
            'sha256',
            $request->getContent(),
            config('services.messenger.app_secret')
        );

        // if (!hash_equals($expected, $signature ?? '')) {
        //     Log::warning('Messenger: assinatura inválida');
        //     return response('Forbidden', 403);
        // }

        // 2. Garante que é evento de página
        if ($request->input('object') !== 'page') {
            Log::info('Messenger ignorado: object != page', [
                'object' => $request->input('object'),
            ]);
            return response()->json(['status' => 'ignored']);
        }

        try {
            foreach ($request->input('entry', []) as $entry) {
                foreach ($entry['messaging'] ?? [] as $messagingEvent) {
                    $this->handleMessagingEvent($messagingEvent);
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Messenger webhook erro: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error']);
        }
    }

    private function handleMessagingEvent(array $messagingEvent): void
    {
        $from = $messagingEvent['sender']['id'] ?? null;
        if (!$from) {
            Log::info('Messenger evento sem sender', ['event' => $messagingEvent]);
            return;
        }

        if (isset($messagingEvent['delivery']) || isset($messagingEvent['read'])) {
            return;
        }

        $sessionQuestionId = Cache::get("msng_question_$from");
        $optionMap = Cache::get("msng_option_map_$from");

        // ── Postback (se messaging_postbacks estiver activo) ────────────
        if (isset($messagingEvent['postback'])) {
            $payload = (string) ($messagingEvent['postback']['payload'] ?? '');

            Log::info('Messenger POSTBACK', [
                'from' => $from,
                'payload' => $payload,
                'session_question_id' => $sessionQuestionId,
                'option_map' => $optionMap,
            ]);

            $this->processPayload($from, $payload);
            return;
        }

        // ── Mensagem de texto / quick_reply ─────────────────────────────
        if (isset($messagingEvent['message'])) {
            if (!empty($messagingEvent['message']['is_echo'])) {
                return;
            }

            $text = (string) ($messagingEvent['message']['text'] ?? '');

            // Quick replies vêm no webhook "messages" (não precisam de postbacks)
            if (isset($messagingEvent['message']['quick_reply'])) {
                $payload = (string) ($messagingEvent['message']['quick_reply']['payload'] ?? '');
                Log::info('Messenger QUICK_REPLY', [
                    'from' => $from,
                    'payload' => $payload,
                    'text' => $text,
                    'session_question_id' => $sessionQuestionId,
                ]);
                $this->processPayload($from, $payload);
                return;
            }

            Log::info('Messenger TEXTO', [
                'from' => $from,
                'text' => $text,
                'session_question_id' => $sessionQuestionId,
                'option_map' => $optionMap,
            ]);

            $this->processText($from, $text);
            return;
        }

        Log::info('Messenger evento desconhecido', [
            'from' => $from,
            'keys' => array_keys($messagingEvent),
        ]);
    }
 
    // ─────────────────────────────────────────────
    // Processa texto livre
    // ─────────────────────────────────────────────
    private function processText(string $from, string $text): \Illuminate\Http\JsonResponse
    {
        $currentQuestionId = Cache::get("msng_question_$from");
 
        // Sem sessão activa
        if (!$currentQuestionId) {
            if ($this->isGreeting($text)) {
                return $this->startMenu($from);
            }
 
            $this->messenger->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
            return response()->json(['status' => 'no_session']);
        }
 
        // Aguardando código de estudante
        $awaiting = Cache::get("msng_awaiting_code_$from");
        if ($awaiting && !empty($text)) {
            return $this->checkStudentCode($from, $text, $awaiting);
        }
 
        $question = QuestionBot::with('options')->find($currentQuestionId);

        // Pergunta aberta (type = text)
        if ($question && $question->type === 'text') {
            return $this->advanceQuestion($from, $question, $question->options->first());
        }

        // Pergunta com opções: aceita número, value, label ou texto do botão
        if ($question) {
            $option = $this->resolveOptionFromPayload($question, $text, $from);
            return $this->applySelectedOption($from, $question, $option);
        }

        return response()->json(['status' => 'waiting']);
    }
 
    // ─────────────────────────────────────────────
    // Processa payload de botão / postback
    // ─────────────────────────────────────────────
    private function processPayload(string $from, string $payload): \Illuminate\Http\JsonResponse
    {
        if (in_array($payload, ['GET_STARTED', 'ajuda', 'menu'], true) || $this->isGreeting($payload)) {
            return $this->startMenu($from);
        }

        $currentQuestionId = Cache::get("msng_question_$from");
        $question          = $currentQuestionId
            ? QuestionBot::with('options')->find($currentQuestionId)
            : null;
 
        // Situação académica
        if ($payload === 'situacao_academica') {
            Cache::put("msng_awaiting_code_$from", 'academica', now()->addMinutes(10));
            $this->messenger->sendText($from, "Insira o seu código de estudante para verificar a situação académica.");
            return response()->json(['status' => 'awaiting_code_academica']);
        }
 
        // Situação financeira
        if ($payload === 'situacao_financeira') {
            Cache::put("msng_awaiting_code_$from", 'financeira', now()->addMinutes(10));
            $this->messenger->sendText($from, "Insira o seu código de estudante para verificar a situação financeira.");
            return response()->json(['status' => 'awaiting_code_financeira']);
        }
 
        // Voltar
        if ($payload === 'voltar') {
            return $this->goBack($from);
        }

        // Paginação de quick replies (Mais / Anterior)
        if ($question && preg_match('/^page:(\d+)$/', $payload, $matches)) {
            $this->sendDynamicQuestion($from, $question, (int) $matches[1]);
            return response()->json(['status' => 'page_changed']);
        }
 
        // Opção do QuestionBot (opt:id, número ou label)
        if ($question && $payload !== '') {
            $option = $this->resolveOptionFromPayload($question, $payload, $from);
            return $this->applySelectedOption($from, $question, $option);
        }

        if ($payload !== '') {
            $this->messenger->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
        }

        return response()->json(['status' => 'unknown_payload']);
    }

    private function applySelectedOption(string $from, QuestionBot $question, ?OptionBot $option): \Illuminate\Http\JsonResponse
    {
        if ($option && $option->value === 'voltar') {
            return $this->goBack($from);
        }

        if ($option) {
            // Evita processar 2x o mesmo clique (postback + texto "1")
            $dedupeKey = "msng_last_opt_$from";
            $token = ($option->id ?? 0) . ':' . ($option->value ?? '');
            if (Cache::get($dedupeKey) === $token) {
                return response()->json(['status' => 'duplicate_ignored']);
            }
            Cache::put($dedupeKey, $token, now()->addSeconds(4));

            return $this->advanceQuestion($from, $question, $option);
        }

        Log::warning('Messenger opção não reconhecida', [
            'from' => $from,
            'question_id' => $question->id,
        ]);
        $this->sendDynamicQuestion($from, $question);
        return response()->json(['status' => 'invalid_option']);
    }
 
    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────
    private function startMenu(string $from): \Illuminate\Http\JsonResponse
    {
        $question = QuestionBot::where('is_start', true)
            ->where('active', true)
            ->with('options')
            ->first();
 
        if (!$question) {
            $this->messenger->sendText($from, "Nenhuma pergunta inicial cadastrada.");
            return response()->json(['status' => 'no_start_question']);
        }
 
        $this->sendDynamicQuestion($from, $question);
        $this->saveHistory($from, $question->id, []);
        return response()->json(['status' => 'start']);
    }
 
    private function goBack(string $from): \Illuminate\Http\JsonResponse
    {
        $history  = Cache::get("msng_history_$from", []);
        array_pop($history);
        $previousId = array_pop($history);
 
        if ($previousId) {
            $prev = QuestionBot::with('options')->find($previousId);
            $this->sendDynamicQuestion($from, $prev);
            Cache::put("msng_question_$from", $previousId, now()->addMinutes(30));
            Cache::put("msng_history_$from", $history, now()->addMinutes(30));
        } else {
            $this->messenger->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
            Cache::forget("msng_question_$from");
            Cache::forget("msng_history_$from");
        }
 
        return response()->json(['status' => 'back']);
    }
 
    private function advanceQuestion(string $from, QuestionBot $current, ?OptionBot $option): \Illuminate\Http\JsonResponse
    {
        if ($option && $option->next_question_bot_id) {
            $next = QuestionBot::where('id', $option->next_question_bot_id)
                ->where('active', true)
                ->with('options')
                ->first();
 
            if ($next) {
                $this->sendDynamicQuestion($from, $next);
                $history   = Cache::get("msng_history_$from", []);
                $history[] = $current->id;
                $this->saveHistory($from, $next->id, $history);
                return response()->json(['status' => 'option_processed']);
            }
        }
 
        $this->messenger->sendText($from, "Obrigado! O seu atendimento foi finalizado.");
        Cache::forget("msng_question_$from");
        Cache::forget("msng_history_$from");
        return response()->json(['status' => 'finished']);
    }
 
    private function checkStudentCode(string $from, string $code, string $type): \Illuminate\Http\JsonResponse
    {
        $student = StudentUcm::where('code', $code)->first();
 
        if ($student) {
            $msg = $type === 'academica'
                ? "Situação académica de {$student->name}: {$student->situacao_academica}"
                : "Situação financeira de {$student->name}: {$student->situacao_financeira}";
        } else {
            $msg = "Código de estudante não encontrado. Tente novamente.";
        }
 
        $this->messenger->sendText($from, $msg);
        Cache::forget("msng_awaiting_code_$from");
        return response()->json(['status' => 'student_code_checked']);
    }
 
    private function sendDynamicQuestion(string $from, QuestionBot $question): void
    {
        if (!$question) {
            return;
        }

        $options = $this->getQuestionOptions($question)->values();
        $count = $options->count();

        if ($count === 0) {
            $this->messenger->sendText($from, $question->text);
            Cache::forget("msng_option_map_$from");
            return;
        }

        // Mapa 1→optionId (ou "voltar") para o utilizador poder responder com número
        $map = [];
        foreach ($options as $index => $opt) {
            $map[$index + 1] = !empty($opt->id) ? (int) $opt->id : (string) $opt->value;
        }
        Cache::put("msng_option_map_$from", $map, now()->addMinutes(30));

        // Lista vertical em texto (o carrossel usa postback e o teu webhook
        // não está a receber messaging_postbacks — só "messages")
        $lines = $options->map(
            fn ($opt, $index) => ($index + 1) . '. ' . ($opt->label ?: $opt->value)
        )->implode("\n");

        $body = $question->text . "\n\n" . $lines . "\n\nToque numa opção ou envie o número.";

        // Quick replies: chegam como message.quick_reply no webhook "messages"
        $quickReplies = $options->take(13)->values()->map(function ($opt, $index) {
            $payload = !empty($opt->id)
                ? 'opt:' . $opt->id
                : (string) $opt->value;

            return [
                'content_type' => 'text',
                'title'        => (string) ($index + 1),
                'payload'      => $payload,
            ];
        })->toArray();

        $this->messenger->sendQuickReplies($from, $body, $quickReplies);
    }

    private function getQuestionOptions(QuestionBot $question)
    {
        $options = $question->options->values();

        if (!($question->is_start ?? false)) {
            $voltar = new OptionBot();
            $voltar->label = 'Voltar';
            $voltar->value = 'voltar';
            $options->push($voltar);
        }

        return $options;
    }

    private function resolveOptionFromPayload(QuestionBot $question, string $input, string $from): ?OptionBot
    {
        $raw = trim($input);
        if ($raw === '') {
            return null;
        }

        $normalized = mb_strtolower($raw);
        $options = $this->getQuestionOptions($question)->values();
        $list = $options->all();

        // 1) Payload do carrossel: opt:{id}
        if (preg_match('/^opt:(\d+)$/i', $raw, $matches)) {
            $optionId = (int) $matches[1];

            foreach ($list as $opt) {
                if ((int) ($opt->id ?? 0) === $optionId) {
                    return $opt;
                }
            }

            return OptionBot::where('question_bot_id', $question->id)
                ->where('id', $optionId)
                ->first();
        }

        // 2) Número (1, 2, 3...) — mapa da sessão ou índice da lista
        if (preg_match('/^\d+$/', $normalized)) {
            $number = (int) $normalized;
            $map = Cache::get("msng_option_map_$from", []);

            if (isset($map[$number])) {
                $mapped = $map[$number];
                if ($mapped === 'voltar') {
                    $voltar = new OptionBot();
                    $voltar->label = 'Voltar';
                    $voltar->value = 'voltar';
                    return $voltar;
                }

                foreach ($list as $opt) {
                    if ((int) ($opt->id ?? 0) === (int) $mapped) {
                        return $opt;
                    }
                }

                $fromDb = OptionBot::where('question_bot_id', $question->id)
                    ->where('id', (int) $mapped)
                    ->first();
                if ($fromDb) {
                    return $fromDb;
                }
            }

            $index = $number - 1;
            if (array_key_exists($index, $list)) {
                return $list[$index];
            }
        }

        // 3) Código do label: "1.1", "3.1", ...
        if (preg_match('/^(\d+\.\d+)/', $normalized, $matches)) {
            $code = $matches[1];
            foreach ($list as $opt) {
                $label = mb_strtolower(trim((string) $opt->label));
                if (preg_match('/^(\d+\.\d+)/', $label, $labelMatch) && $labelMatch[1] === $code) {
                    return $opt;
                }
            }
        }

        // 4) value / label / texto do botão
        $cleanInput = $this->normalizeOptionText($normalized);

        foreach ($list as $opt) {
            $label = mb_strtolower((string) $opt->label);
            $value = mb_strtolower((string) $opt->value);
            $cleanLabel = $this->normalizeOptionText($label);

            if ($value === $normalized || $label === $normalized) {
                return $opt;
            }

            if ($cleanInput !== '' && (
                $cleanLabel === $cleanInput
                || str_starts_with($cleanLabel, $cleanInput)
                || str_starts_with($cleanInput, $cleanLabel)
            )) {
                return $opt;
            }
        }

        return null;
    }

    private function normalizeOptionText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = str_replace(['…', '...'], '', $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return rtrim($text, " \t.");
    }

    private function isGreeting(string $text): bool
    {
        return in_array(mb_strtolower(trim($text)), ['olá', 'oi', 'ajuda', 'ola', 'menu'], true);
    }
 
    private function saveHistory(string $from, int $questionId, array $history): void
    {
        Cache::put("msng_question_$from", $questionId, now()->addMinutes(30));
        Cache::put("msng_history_$from", $history, now()->addMinutes(30));
    }
}
