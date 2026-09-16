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
        // 1. Valida assinatura HMAC
        $signature = $request->header('X-Hub-Signature-256');
        $expected  = 'sha256=' . hash_hmac(
            'sha256',
            $request->getContent(),
            config('services.messenger.app_secret')
        );

        // Adiciona isto temporariamente no início do método handle()
        // Log::info('Raw body: ' . $request->getContent());
        // Log::info('Signature header: ' . $request->header('X-Hub-Signature-256'));
        // Log::info('App secret: ' . substr(config('services.messenger.app_secret'), 0, 6) . '...');
 
        // if (!hash_equals($expected, $signature ?? '')) {
        //     Log::warning('Messenger: assinatura inválida');
        //     return response('Forbidden', 403);
        // }
 
        // 2. Garante que é evento de página
        if ($request->input('object') !== 'page') {
            return response()->json(['status' => 'ignored']);
        }
 
        try {
            $entry          = $request->input('entry')[0] ?? null;
            $messagingEvent = $entry['messaging'][0] ?? null;
 
            if (!$messagingEvent) {
                return response()->json(['status' => 'no_event']);
            }
 
            $from = $messagingEvent['sender']['id'];

            $this->messenger->markSeen($from);
            $this->messenger->typingOn($from);
 
            // ── Postback (botões com payload / Get Started) ────────────
            if (isset($messagingEvent['postback'])) {
                $payload = $messagingEvent['postback']['payload'] ?? '';
                Log::info('Messenger postback', ['from' => $from, 'payload' => $payload]);
                return $this->processPayload($from, $payload);
            }
 
            // ── Mensagem de texto ──────────────────────────────────────
            if (isset($messagingEvent['message'])) {
                if (!empty($messagingEvent['message']['is_echo'])) {
                    return response()->json(['status' => 'echo']);
                }

                $text = $messagingEvent['message']['text'] ?? '';
 
                // Botões quick_reply também têm payload
                if (isset($messagingEvent['message']['quick_reply'])) {
                    $payload = $messagingEvent['message']['quick_reply']['payload'] ?? '';
                    return $this->processPayload($from, $payload);
                }
 
                return $this->processText($from, $text);
            }
 
            return response()->json(['status' => 'waiting']);
 
        } catch (\Throwable $e) {
            Log::error('Messenger webhook erro: ' . $e->getMessage());
            return response()->json(['status' => 'error']);
        }
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
            $option = $this->resolveOptionFromPayload($question, $text);
            if ($option && $option->value === 'voltar') {
                return $this->goBack($from);
            }
            if ($option) {
                return $this->advanceQuestion($from, $question, $option);
            }

            $this->sendDynamicQuestion($from, $question);
            return response()->json(['status' => 'invalid_option']);
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
 
        // Opção do QuestionBot (opt:id, value, número ou label)
        if ($question && $payload !== '') {
            $option = $this->resolveOptionFromPayload($question, $payload);

            if ($option && $option->value === 'voltar') {
                return $this->goBack($from);
            }

            if ($option) {
                return $this->advanceQuestion($from, $question, $option);
            }

            // Clique não reconhecido: reenvia as opções (em vez de silêncio)
            Log::warning('Messenger payload não reconhecido', [
                'from' => $from,
                'payload' => $payload,
                'question_id' => $question->id,
            ]);
            $this->sendDynamicQuestion($from, $question);
            return response()->json(['status' => 'unknown_payload']);
        }

        if ($payload !== '') {
            $this->messenger->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
        }

        return response()->json(['status' => 'unknown_payload']);
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
            Cache::put("msng_question_$from", $previousId, now()->addMinutes(10));
            Cache::put("msng_history_$from", $history, now()->addMinutes(10));
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

        $options = $this->getQuestionOptions($question);
        $count = $options->count();

        if ($count === 0) {
            $this->messenger->sendText($from, $question->text);
            return;
        }

        $this->messenger->sendText($from, $question->text);

        $elements = $options->take(10)->values()->map(function ($opt) {
            $label = $opt->label ?: $opt->value;

            // Payload estável por id (evita falhas com labels longos / value estranho)
            $payload = !empty($opt->id)
                ? 'opt:' . $opt->id
                : (string) $opt->value;

            return [
                'title'    => $this->messenger->truncateTitle($label, 80),
                'subtitle' => 'Toque no botão para escolher',
                'buttons'  => [[
                    'type'    => 'postback',
                    'title'   => $this->messenger->truncateTitle($label, 20),
                    'payload' => $payload,
                ]],
            ];
        })->toArray();

        $this->messenger->sendGenericTemplate($from, $elements);
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

    private function resolveOptionFromPayload(QuestionBot $question, string $input): ?OptionBot
    {
        $raw = trim($input);
        $normalized = mb_strtolower($raw);
        if ($normalized === '') {
            return null;
        }

        $options = $this->getQuestionOptions($question)->values();

        // Payload do carrossel: opt:{id}
        if (preg_match('/^opt:(\d+)$/i', $raw, $matches)) {
            $optionId = (int) $matches[1];
            $byId = $options->firstWhere('id', $optionId);
            if ($byId) {
                return $byId;
            }

            return OptionBot::where('question_bot_id', $question->id)
                ->where('id', $optionId)
                ->first();
        }

        // Só número: 1, 2, 3...
        if (preg_match('/^\d+$/', $normalized)) {
            $index = ((int) $normalized) - 1;
            if (isset($options[$index])) {
                return $options[$index];
            }
        }

        // Código tipo "1.1" / "1.1 como fazer insc…" (labels do teu menu)
        if (preg_match('/^(\d+(?:\.\d+)+)/', $normalized, $matches)) {
            $code = $matches[1];
            $byCode = $options->first(
                fn ($opt) => str_starts_with(mb_strtolower((string) $opt->label), $code)
            );
            if ($byCode) {
                return $byCode;
            }
        }

        $cleanInput = $this->normalizeOptionText($normalized);

        return $options->first(function ($opt) use ($normalized, $cleanInput) {
            $label = mb_strtolower((string) $opt->label);
            $value = mb_strtolower((string) $opt->value);
            $buttonTitle = mb_strtolower(
                $this->messenger->truncateTitle((string) ($opt->label ?: $opt->value), 20)
            );
            $cleanLabel = $this->normalizeOptionText($label);
            $cleanButton = $this->normalizeOptionText($buttonTitle);

            return $value === $normalized
                || $label === $normalized
                || $buttonTitle === $normalized
                || ($cleanInput !== '' && (
                    $cleanLabel === $cleanInput
                    || $cleanButton === $cleanInput
                    || str_starts_with($cleanLabel, $cleanInput)
                    || str_starts_with($cleanInput, $cleanButton)
                ));
        });
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
        Cache::put("msng_question_$from", $questionId, now()->addMinutes(10));
        Cache::put("msng_history_$from", $history, now()->addMinutes(10));
    }
}
