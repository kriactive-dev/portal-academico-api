<?php

namespace App\Http\Controllers\Api\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\ChatBot\QuestionBot;
use App\Models\ChatBot\OptionBot;
use App\Models\Student\StudentUcm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebChatBotController extends Controller
{
    /**
     * Inicializar conversa do chatbot web
     * Retorna a primeira pergunta (is_start = true)
     */
    public function initChat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|max:255',
                'user_identifier' => 'nullable|string|max:255', // pode ser email, telefone, etc
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;
            $userIdentifier = $request->user_identifier;

            // Limpar sessão anterior se existir
            Cache::forget("web_current_question_$sessionId");
            Cache::forget("web_question_history_$sessionId");
            Cache::forget("web_awaiting_student_code_$sessionId");

            // Buscar pergunta inicial
            $question = QuestionBot::where('is_start', true)
                ->where('active', true)
                ->with(['options.nextQuestion'])
                ->first();

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma pergunta inicial encontrada.',
                    'data' => null
                ], 404);
            }

            // Salvar na cache
            Cache::put("web_current_question_$sessionId", $question->id, now()->addMinutes(30));
            Cache::put("web_question_history_$sessionId", [$question->id], now()->addMinutes(30));

            return response()->json([
                'success' => true,
                'message' => 'Conversa iniciada com sucesso.',
                'data' => $this->formatQuestionResponse($question, $sessionId)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao inicializar chat web: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Processar resposta do usuário
     */
    public function processMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|max:255',
                'message' => 'nullable|string|max:1000',
                'option_value' => 'nullable|string|max:255',
                'action' => 'nullable|string|in:back,restart',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;
            $message = $request->message;
            $optionValue = $request->option_value;
            $action = $request->action;

            // Verificar se há sessão ativa
            $currentQuestionId = Cache::get("web_current_question_$sessionId");
            if (!$currentQuestionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sessão expirada. Inicie uma nova conversa.',
                    'data' => null
                ], 404);
            }

            // Processar ação especial (voltar ou reiniciar)
            if ($action === 'back') {
                return $this->handleBackAction($sessionId);
            }

            if ($action === 'restart') {
                return $this->handleRestartAction($sessionId);
            }

            // Verificar se está aguardando código de estudante
            $awaiting = Cache::get("web_awaiting_student_code_$sessionId");
            if ($awaiting && !empty($message)) {
                return $this->handleStudentCodeCheck($sessionId, $message, $awaiting);
            }

            $currentQuestion = QuestionBot::with(['options.nextQuestion'])->find($currentQuestionId);
            if (!$currentQuestion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pergunta atual não encontrada.',
                    'data' => null
                ], 404);
            }

            // Processar opção selecionada
            if (!empty($optionValue)) {
                return $this->handleOptionSelection($sessionId, $currentQuestion, $optionValue);
            }

            // Processar mensagem de texto (para perguntas abertas)
            if (!empty($message) && $currentQuestion->type === 'text') {
                return $this->handleTextMessage($sessionId, $currentQuestion, $message);
            }

            return response()->json([
                'success' => false,
                'message' => 'Nenhuma resposta válida fornecida.',
                'data' => null
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem web: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Obter pergunta atual da sessão
     */
    public function getCurrentQuestion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;
            $currentQuestionId = Cache::get("web_current_question_$sessionId");

            if (!$currentQuestionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma sessão ativa encontrada.',
                    'data' => null
                ], 404);
            }

            $question = QuestionBot::with(['options.nextQuestion'])->find($currentQuestionId);
            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pergunta atual não encontrada.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pergunta atual recuperada com sucesso.',
                'data' => $this->formatQuestionResponse($question, $sessionId)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter pergunta atual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Encerrar sessão do chat
     */
    public function endChat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;

            // Limpar cache da sessão
            Cache::forget("web_current_question_$sessionId");
            Cache::forget("web_question_history_$sessionId");
            Cache::forget("web_awaiting_student_code_$sessionId");

            return response()->json([
                'success' => true,
                'message' => 'Sessão encerrada com sucesso.',
                'data' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao encerrar chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Listar histórico da conversa
     */
    public function getChatHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sessionId = $request->session_id;
            $history = Cache::get("web_question_history_$sessionId", []);

            if (empty($history)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nenhum histórico encontrado.',
                    'data' => []
                ]);
            }

            $questions = QuestionBot::with(['options'])->whereIn('id', $history)->get();

            return response()->json([
                'success' => true,
                'message' => 'Histórico recuperado com sucesso.',
                'data' => $questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'text' => $question->text,
                        'type' => $question->type,
                        'options' => $question->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'label' => $option->label,
                                'value' => $option->value
                            ];
                        })
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter histórico: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'data' => null
            ], 500);
        }
    }

    // Métodos privados auxiliares

    private function handleBackAction($sessionId)
    {
        $history = Cache::get("web_question_history_$sessionId", []);
        
        if (count($history) <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível voltar. Esta é a primeira pergunta.',
                'data' => null
            ], 400);
        }

        array_pop($history); // Remove pergunta atual
        $previousQuestionId = array_pop($history); // Pega a anterior
        
        $previousQuestion = QuestionBot::with(['options.nextQuestion'])->find($previousQuestionId);
        if (!$previousQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'Pergunta anterior não encontrada.',
                'data' => null
            ], 404);
        }

        // Atualizar cache
        Cache::put("web_current_question_$sessionId", $previousQuestionId, now()->addMinutes(30));
        Cache::put("web_question_history_$sessionId", array_merge($history, [$previousQuestionId]), now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'message' => 'Voltou para pergunta anterior.',
            'data' => $this->formatQuestionResponse($previousQuestion, $sessionId)
        ]);
    }

    private function handleRestartAction($sessionId)
    {
        // Limpar cache
        Cache::forget("web_current_question_$sessionId");
        Cache::forget("web_question_history_$sessionId");
        Cache::forget("web_awaiting_student_code_$sessionId");

        // Buscar pergunta inicial
        $question = QuestionBot::where('is_start', true)
            ->where('active', true)
            ->with(['options.nextQuestion'])
            ->first();

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma pergunta inicial encontrada.',
                'data' => null
            ], 404);
        }

        // Salvar na cache
        Cache::put("web_current_question_$sessionId", $question->id, now()->addMinutes(30));
        Cache::put("web_question_history_$sessionId", [$question->id], now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'message' => 'Chat reiniciado com sucesso.',
            'data' => $this->formatQuestionResponse($question, $sessionId)
        ]);
    }

    private function handleStudentCodeCheck($sessionId, $studentCode, $awaiting)
    {
        $student = StudentUcm::where('code', $studentCode)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Código de estudante não encontrado.',
                'data' => [
                    'type' => 'student_code_error',
                    'awaiting_type' => $awaiting
                ]
            ], 404);
        }

        $response = [];
        if ($awaiting === 'academica') {
            $response = [
                'type' => 'student_info',
                'student' => [
                    'name' => $student->name,
                    'code' => $student->code,
                    'situacao_academica' => $student->situacao_academica
                ],
                'message' => "Situação acadêmica do estudante {$student->name}: {$student->situacao_academica}"
            ];
        } elseif ($awaiting === 'financeira') {
            $response = [
                'type' => 'student_info',
                'student' => [
                    'name' => $student->name,
                    'code' => $student->code,
                    'situacao_financeira' => $student->situacao_financeira
                ],
                'message' => "Situação financeira do estudante {$student->name}: {$student->situacao_financeira}"
            ];
        }

        // Limpar estado de espera
        Cache::forget("web_awaiting_student_code_$sessionId");

        return response()->json([
            'success' => true,
            'message' => 'Informações do estudante recuperadas com sucesso.',
            'data' => $response
        ]);
    }

    private function handleOptionSelection($sessionId, $currentQuestion, $optionValue)
    {
        // Situações especiais
        if ($optionValue === 'situacao_academica') {
            Cache::put("web_awaiting_student_code_$sessionId", 'academica', now()->addMinutes(5));
            return response()->json([
                'success' => true,
                'message' => 'Aguardando código do estudante.',
                'data' => [
                    'type' => 'awaiting_student_code',
                    'awaiting_type' => 'academica',
                    'message' => 'Por favor, insira seu código de estudante para verificar sua situação acadêmica.'
                ]
            ]);
        }

        if ($optionValue === 'situacao_financeira') {
            Cache::put("web_awaiting_student_code_$sessionId", 'financeira', now()->addMinutes(5));
            return response()->json([
                'success' => true,
                'message' => 'Aguardando código do estudante.',
                'data' => [
                    'type' => 'awaiting_student_code',
                    'awaiting_type' => 'financeira',
                    'message' => 'Por favor, insira seu código de estudante para verificar sua situação financeira.'
                ]
            ]);
        }

        // Buscar opção selecionada
        $option = OptionBot::where('question_bot_id', $currentQuestion->id)
            ->where('value', $optionValue)
            ->first();

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Opção não encontrada.',
                'data' => null
            ], 404);
        }

        // Se há próxima pergunta
        if ($option->next_question_bot_id) {
            $nextQuestion = QuestionBot::where('id', $option->next_question_bot_id)
                ->where('active', true)
                ->with(['options.nextQuestion'])
                ->first();

            if ($nextQuestion) {
                // Atualizar histórico e pergunta atual
                $history = Cache::get("web_question_history_$sessionId", []);
                $history[] = $nextQuestion->id;
                Cache::put("web_question_history_$sessionId", $history, now()->addMinutes(30));
                Cache::put("web_current_question_$sessionId", $nextQuestion->id, now()->addMinutes(30));

                return response()->json([
                    'success' => true,
                    'message' => 'Próxima pergunta carregada.',
                    'data' => $this->formatQuestionResponse($nextQuestion, $sessionId)
                ]);
            }
        }

        // Fim da conversa
        Cache::forget("web_current_question_$sessionId");
        Cache::forget("web_question_history_$sessionId");

        return response()->json([
            'success' => true,
            'message' => 'Conversa finalizada.',
            'data' => [
                'type' => 'conversation_end',
                'message' => 'Obrigado! Seu atendimento foi finalizado.'
            ]
        ]);
    }

    private function handleTextMessage($sessionId, $currentQuestion, $message)
    {
        // Para pergunta de texto, pegar primeira opção (se existir)
        $option = $currentQuestion->options->first();

        if ($option && $option->next_question_bot_id) {
            $nextQuestion = QuestionBot::where('id', $option->next_question_bot_id)
                ->where('active', true)
                ->with(['options.nextQuestion'])
                ->first();

            if ($nextQuestion) {
                // Atualizar histórico
                $history = Cache::get("web_question_history_$sessionId", []);
                $history[] = $nextQuestion->id;
                Cache::put("web_question_history_$sessionId", $history, now()->addMinutes(30));
                Cache::put("web_current_question_$sessionId", $nextQuestion->id, now()->addMinutes(30));

                return response()->json([
                    'success' => true,
                    'message' => 'Resposta recebida. Próxima pergunta carregada.',
                    'data' => $this->formatQuestionResponse($nextQuestion, $sessionId)
                ]);
            }
        }

        // Fim da conversa
        Cache::forget("web_current_question_$sessionId");
        Cache::forget("web_question_history_$sessionId");

        return response()->json([
            'success' => true,
            'message' => 'Resposta recebida. Conversa finalizada.',
            'data' => [
                'type' => 'conversation_end',
                'message' => 'Obrigado pela sua resposta! Seu atendimento foi finalizado.'
            ]
        ]);
    }

    private function formatQuestionResponse($question, $sessionId)
    {
        $history = Cache::get("web_question_history_$sessionId", []);
        
        return [
            'question' => [
                'id' => $question->id,
                'text' => $question->text,
                'type' => $question->type,
                'is_start' => $question->is_start ?? false
            ],
            'options' => $question->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'label' => $option->label,
                    'value' => $option->value,
                    'has_next_question' => !is_null($option->next_question_bot_id)
                ];
            }),
            'session_info' => [
                'can_go_back' => count($history) > 1,
                'question_count' => count($history),
                'session_id' => $sessionId
            ]
        ];
    }
}