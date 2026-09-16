<?php

namespace App\Http\Controllers\Api\ChatBot\Instagram;

use App\Http\Controllers\Controller;
use App\Models\ChatBot\OptionBot;
use App\Models\ChatBot\QuestionBot;
use App\Models\Student\StudentUcm;
use App\Services\ChatBot\Instagram\InstagramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InstagramBotController extends Controller
{
    public function __construct(protected InstagramService $instagram) {}

    // ─────────────────────────────────────────────
    // GET /webhook/instagram  →  verificação Meta
    // ─────────────────────────────────────────────
    public function verify(Request $request)
    {
        $verifyToken = config('services.instagram.verify_token');
        $mode = $request->hub_mode ?? $request->query('hub.mode');
        $token = $request->hub_verify_token ?? $request->query('hub.verify_token');
        $challenge = $request->hub_challenge ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && hash_equals((string) $verifyToken, (string) $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Erro de verificação', 403);
    }

    // ─────────────────────────────────────────────
    // POST /webhook/instagram  →  eventos recebidos
    // ─────────────────────────────────────────────
    public function handle(Request $request)
    {
        $object = $request->input('object');

        if (!in_array($object, ['instagram', 'page'], true)) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            foreach ($request->input('entry', []) as $entry) {
                foreach ($entry['messaging'] ?? [] as $messagingEvent) {
                    $this->handleMessagingEvent($messagingEvent);
                }

                foreach ($entry['changes'] ?? [] as $change) {
                    $value = $change['value'] ?? null;
                    if (is_array($value) && (isset($value['sender']) || isset($value['message']))) {
                        $this->handleMessagingEvent($value);
                    }
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Instagram webhook erro: ' . $e->getMessage());
            return response()->json(['status' => 'error']);
        }
    }

    private function handleMessagingEvent(array $messagingEvent): void
    {
        $from = $messagingEvent['sender']['id'] ?? null;
        if (!$from) {
            return;
        }

        if (isset($messagingEvent['delivery']) || isset($messagingEvent['read'])) {
            return;
        }

        if (isset($messagingEvent['postback'])) {
            $payload = (string) ($messagingEvent['postback']['payload'] ?? '');
            $this->processPayload($from, $payload);
            return;
        }

        if (isset($messagingEvent['message'])) {
            if (!empty($messagingEvent['message']['is_echo'])) {
                return;
            }

            $text = (string) ($messagingEvent['message']['text'] ?? '');

            if (isset($messagingEvent['message']['quick_reply'])) {
                $payload = (string) ($messagingEvent['message']['quick_reply']['payload'] ?? '');
                $this->processPayload($from, $payload);
                return;
            }

            $this->processText($from, $text);
        }
    }

    private function processText(string $from, string $text): \Illuminate\Http\JsonResponse
    {
        $currentQuestionId = Cache::get("ig_question_$from");

        if (!$currentQuestionId) {
            if ($this->isGreeting($text)) {
                return $this->startMenu($from);
            }

            $this->instagram->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
            return response()->json(['status' => 'no_session']);
        }

        $awaiting = Cache::get("ig_awaiting_code_$from");
        if ($awaiting && !empty($text)) {
            return $this->checkStudentCode($from, $text, $awaiting);
        }

        $question = QuestionBot::with('options')->find($currentQuestionId);

        if ($question && $question->type === 'text') {
            return $this->advanceQuestion($from, $question, $question->options->first());
        }

        if ($question) {
            $option = $this->resolveOptionFromPayload($question, $text, $from);
            return $this->applySelectedOption($from, $question, $option);
        }

        return response()->json(['status' => 'waiting']);
    }

    private function processPayload(string $from, string $payload): \Illuminate\Http\JsonResponse
    {
        if (in_array($payload, ['GET_STARTED', 'ajuda', 'menu'], true) || $this->isGreeting($payload)) {
            return $this->startMenu($from);
        }

        $currentQuestionId = Cache::get("ig_question_$from");
        $question = $currentQuestionId
            ? QuestionBot::with('options')->find($currentQuestionId)
            : null;

        if ($payload === 'situacao_academica') {
            Cache::put("ig_awaiting_code_$from", 'academica', now()->addMinutes(10));
            $this->instagram->sendText($from, "Insira o seu código de estudante para verificar a situação académica.");
            return response()->json(['status' => 'awaiting_code_academica']);
        }

        if ($payload === 'situacao_financeira') {
            Cache::put("ig_awaiting_code_$from", 'financeira', now()->addMinutes(10));
            $this->instagram->sendText($from, "Insira o seu código de estudante para verificar a situação financeira.");
            return response()->json(['status' => 'awaiting_code_financeira']);
        }

        if ($payload === 'voltar') {
            return $this->goBack($from);
        }

        if ($question && preg_match('/^page:(\d+)$/', $payload, $matches)) {
            $this->sendDynamicQuestion($from, $question, (int) $matches[1]);
            return response()->json(['status' => 'page_changed']);
        }

        if ($question && $payload !== '') {
            $option = $this->resolveOptionFromPayload($question, $payload, $from);
            return $this->applySelectedOption($from, $question, $option);
        }

        if ($payload !== '') {
            $this->instagram->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
        }

        return response()->json(['status' => 'unknown_payload']);
    }

    private function applySelectedOption(string $from, QuestionBot $question, ?OptionBot $option): \Illuminate\Http\JsonResponse
    {
        if ($option && $option->value === 'voltar') {
            return $this->goBack($from);
        }

        if ($option) {
            $dedupeKey = "ig_last_opt_$from";
            $token = ($option->id ?? 0) . ':' . ($option->value ?? '');
            if (Cache::get($dedupeKey) === $token) {
                return response()->json(['status' => 'duplicate_ignored']);
            }
            Cache::put($dedupeKey, $token, now()->addSeconds(4));

            return $this->advanceQuestion($from, $question, $option);
        }

        $this->sendDynamicQuestion($from, $question, (int) Cache::get("ig_page_$from", 0));
        return response()->json(['status' => 'invalid_option']);
    }

    private function startMenu(string $from): \Illuminate\Http\JsonResponse
    {
        $question = QuestionBot::where('is_start', true)
            ->where('active', true)
            ->with('options')
            ->first();

        if (!$question) {
            $this->instagram->sendText($from, "Nenhuma pergunta inicial cadastrada.");
            return response()->json(['status' => 'no_start_question']);
        }

        $this->sendDynamicQuestion($from, $question);
        $this->saveHistory($from, $question->id, []);
        return response()->json(['status' => 'start']);
    }

    private function goBack(string $from): \Illuminate\Http\JsonResponse
    {
        $history = Cache::get("ig_history_$from", []);
        array_pop($history);
        $previousId = array_pop($history);

        if ($previousId) {
            $prev = QuestionBot::with('options')->find($previousId);
            $this->sendDynamicQuestion($from, $prev);
            Cache::put("ig_question_$from", $previousId, now()->addMinutes(30));
            Cache::put("ig_history_$from", $history, now()->addMinutes(30));
        } else {
            $this->instagram->sendText($from, "Olá! Digite 'ajuda' para receber opções.");
            Cache::forget("ig_question_$from");
            Cache::forget("ig_history_$from");
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
                $history = Cache::get("ig_history_$from", []);
                $history[] = $current->id;
                $this->saveHistory($from, $next->id, $history);
                return response()->json(['status' => 'option_processed']);
            }
        }

        $this->instagram->sendText($from, "Obrigado! O seu atendimento foi finalizado.");
        Cache::forget("ig_question_$from");
        Cache::forget("ig_history_$from");
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

        $this->instagram->sendText($from, $msg);
        Cache::forget("ig_awaiting_code_$from");
        return response()->json(['status' => 'student_code_checked']);
    }

    private function sendDynamicQuestion(string $from, QuestionBot $question, int $page = 0): void
    {
        if (!$question) {
            return;
        }

        $options = $this->getQuestionOptions($question)->values();
        $total = $options->count();

        if ($total === 0) {
            $this->instagram->sendText($from, $question->text);
            Cache::forget("ig_option_map_$from");
            Cache::forget("ig_page_$from");
            return;
        }

        $map = [];
        foreach ($options as $index => $opt) {
            $map[$index + 1] = !empty($opt->id) ? (int) $opt->id : (string) $opt->value;
        }
        Cache::put("ig_option_map_$from", $map, now()->addMinutes(30));

        $perPage = 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(0, min($page, $totalPages - 1));
        $offset = $page * $perPage;
        $slice = $options->slice($offset, $perPage)->values();
        $hasPrev = $page > 0;
        $hasNext = ($offset + $perPage) < $total;

        Cache::put("ig_page_$from", $page, now()->addMinutes(30));

        $lines = $slice->map(function ($opt, $i) use ($offset) {
            $number = $offset + $i + 1;
            return $number . '. ' . ($opt->label ?: $opt->value);
        })->implode("\n");

        $body = $question->text . "\n\n" . $lines;
        if ($totalPages > 1) {
            $body .= "\n\nPágina " . ($page + 1) . "/" . $totalPages;
        }
        $body .= "\n\nToque numa opção ou envie o número.";

        $quickReplies = $slice->map(function ($opt, $i) use ($offset) {
            $number = $offset + $i + 1;
            $payload = !empty($opt->id)
                ? 'opt:' . $opt->id
                : (string) $opt->value;

            return [
                'content_type' => 'text',
                'title'        => (string) $number,
                'payload'      => $payload,
            ];
        })->values()->toArray();

        if ($hasPrev) {
            $quickReplies[] = [
                'content_type' => 'text',
                'title'        => '« Anterior',
                'payload'      => 'page:' . ($page - 1),
            ];
        }

        if ($hasNext) {
            $quickReplies[] = [
                'content_type' => 'text',
                'title'        => 'Mais »',
                'payload'      => 'page:' . ($page + 1),
            ];
        }

        $this->instagram->sendQuickReplies($from, $body, $quickReplies);
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

        if (preg_match('/^\d+$/', $normalized)) {
            $number = (int) $normalized;
            $map = Cache::get("ig_option_map_$from", []);

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

        if (preg_match('/^(\d+\.\d+)/', $normalized, $matches)) {
            $code = $matches[1];
            foreach ($list as $opt) {
                $label = mb_strtolower(trim((string) $opt->label));
                if (preg_match('/^(\d+\.\d+)/', $label, $labelMatch) && $labelMatch[1] === $code) {
                    return $opt;
                }
            }
        }

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
        Cache::put("ig_question_$from", $questionId, now()->addMinutes(30));
        Cache::put("ig_history_$from", $history, now()->addMinutes(30));
    }
}
