<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\Notification\LocalNotificationService;
use Kreait\Firebase\Factory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;

class NotificationController extends Controller
{
    // protected LocalNotificationService $localNotificationService;

    // public function __construct(LocalNotificationService $localNotificationService)
    // {
    //     $this->localNotificationService = $localNotificationService;
    // }

    /**
     * Envia notificação Firebase (método existente)
     */
    public function send(Request $request)
    {
        try {
            // Validação dos dados de entrada
            $request->validate([
                'token' => 'required|string',
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:1000',
            ]);

            // Debug das configurações Firebase
            $projectId = config('firebase.project_id');
            Log::info('Firebase Project ID configurado: ' . $projectId);
            
            if (empty($projectId)) {
                throw new Exception('Project ID do Firebase não configurado');
            }

            // Verifica se o arquivo de credenciais existe
            $credentialsPath = config('firebase.credentials');
            Log::info('Caminho inicial das credenciais: ' . $credentialsPath);
            
            // Se o caminho não for absoluto, tenta diferentes localizações
            if (!file_exists($credentialsPath)) {
                // Tenta na pasta storage/app/firebase/
                $storagePath = storage_path('app/firebase/firebase-credentials.json');
                if (file_exists($storagePath)) {
                    $credentialsPath = $storagePath;
                } else {
                    // Tenta na pasta public/
                    $publicPath = public_path('firebase-credentials.json');
                    if (file_exists($publicPath)) {
                        $credentialsPath = $publicPath;
                    } else {
                        throw new Exception('Arquivo de credenciais Firebase não encontrado');
                    }
                }
            }

            // Verifica se o arquivo é legível
            if (!is_readable($credentialsPath)) {
                throw new Exception('Arquivo de credenciais Firebase não pode ser lido. Verifique as permissões.');
            }

            // Verifica se o arquivo é um JSON válido
            $credentialsContent = file_get_contents($credentialsPath);
            $credentialsJson = json_decode($credentialsContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Arquivo de credenciais Firebase contém JSON inválido: ' . json_last_error_msg());
            }

            // Verifica se contém as chaves necessárias
            $requiredKeys = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
            foreach ($requiredKeys as $key) {
                if (!isset($credentialsJson[$key])) {
                    throw new Exception("Arquivo de credenciais Firebase não contém a chave necessária: {$key}");
                }
            }

            // Verifica se o project_id do arquivo bate com a configuração
            if ($credentialsJson['project_id'] !== $projectId) {
                Log::warning('Project ID do arquivo de credenciais não bate com a configuração', [
                    'config_project_id' => $projectId,
                    'credentials_project_id' => $credentialsJson['project_id']
                ]);
            }

            Log::info('Usando credenciais Firebase do caminho: ' . $credentialsPath, [
                'file_size' => filesize($credentialsPath),
                'project_id_from_file' => $credentialsJson['project_id'] ?? 'não encontrado',
                'client_email' => $credentialsJson['client_email'] ?? 'não encontrado',
                'server_time' => date('c'),
                'server_timezone' => date_default_timezone_get()
            ]);

            // Verificação adicional para problemas de tempo
            $now = time();
            $jwt_iat = $now;
            $jwt_exp = $now + 3600; // 1 hora
            
            Log::info('JWT timing info', [
                'current_timestamp' => $now,
                'jwt_issued_at' => $jwt_iat,
                'jwt_expires_at' => $jwt_exp,
                'server_date' => date('Y-m-d H:i:s T'),
                'utc_date' => gmdate('Y-m-d H:i:s T')
            ]);

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            $messaging = $factory->createMessaging();

            $token = $request->token;
            $title = $request->title;
            $body = $request->body;

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body));

            $messaging->send($message);

            Log::info('Notificação Firebase enviada com sucesso', [
                'token' => substr($token, 0, 20) . '...', // Log parcial do token por segurança
                'title' => $title,
                'project_id' => $projectId
            ]);

            return response()->json(['success' => true, 'message' => 'Notificação enviada com sucesso']);

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            $errorMessage = $e->getMessage();
            
            // Tratamento específico para invalid_grant
            if (strpos($errorMessage, 'invalid_grant') !== false) {
                Log::error('Erro Firebase - invalid_grant detectado', [
                    'error_message' => $errorMessage,
                    'server_time' => date('c'),
                    'server_timezone' => date_default_timezone_get(),
                    'utc_time' => gmdate('c'),
                    'timestamp_diff' => time() - strtotime('now'),
                    'suggested_solution' => 'Verificar sincronização de horário do servidor ou regenerar credenciais',
                    'project_id' => config('firebase.project_id'),
                    'credentials_path' => $credentialsPath ?? 'não definido'
                ]);
                
                return response()->json([
                    'success' => false, 
                    'error' => 'Erro de autenticação Firebase (invalid_grant). Possíveis causas: horário do servidor dessincronizado ou credenciais inválidas.',
                    'debug_info' => [
                        'server_time' => date('c'),
                        'utc_time' => gmdate('c'),
                        'suggestion' => 'Verificar sincronização de horário ou regenerar credenciais Firebase'
                    ]
                ], 401);
            }
            
            Log::error('Erro Firebase - Mensagem inválida: ' . $errorMessage, [
                'error_code' => $e->getCode(),
                'project_id' => config('firebase.project_id'),
                'credentials_path' => $credentialsPath ?? 'não definido'
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Mensagem inválida: ' . $errorMessage
            ], 400);
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            Log::error('Erro Firebase - Autenticação: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'project_id' => config('firebase.project_id'),
                'credentials_path' => $credentialsPath ?? 'não definido',
                'server_time' => date('Y-m-d H:i:s T')
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Erro de autenticação Firebase: ' . $e->getMessage()
            ], 401);
        } catch (\Throwable $th) {
            Log::error('Erro ao enviar notificação Firebase: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
                'project_id' => config('firebase.project_id'),
                'credentials_path' => $credentialsPath ?? 'não definido'
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Erro interno: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Diagnóstica as configurações Firebase
     */
    public function diagnose(Request $request)
    {
        try {
            $diagnostics = [];
            
            // Verificar configurações
            $projectId = config('firebase.project_id');
            $credentialsConfigPath = config('firebase.credentials');
            
            $diagnostics['config'] = [
                'project_id' => $projectId ?: 'NÃO CONFIGURADO',
                'credentials_path_config' => $credentialsConfigPath ?: 'NÃO CONFIGURADO'
            ];

            // Verificar arquivo de credenciais
            $credentialsPath = $credentialsConfigPath;
            
            if (!file_exists($credentialsPath)) {
                $storagePath = storage_path('app/firebase/firebase-credentials.json');
                $publicPath = public_path('firebase-credentials.json');
                
                $diagnostics['file_search'] = [
                    'original_path_exists' => false,
                    'storage_path' => $storagePath,
                    'storage_exists' => file_exists($storagePath),
                    'public_path' => $publicPath,
                    'public_exists' => file_exists($publicPath)
                ];
                
                if (file_exists($storagePath)) {
                    $credentialsPath = $storagePath;
                } elseif (file_exists($publicPath)) {
                    $credentialsPath = $publicPath;
                }
            } else {
                $diagnostics['file_search'] = [
                    'original_path_exists' => true,
                    'path_used' => $credentialsPath
                ];
            }

            if (file_exists($credentialsPath)) {
                $diagnostics['file_info'] = [
                    'path' => $credentialsPath,
                    'exists' => true,
                    'readable' => is_readable($credentialsPath),
                    'size' => filesize($credentialsPath),
                    'permissions' => substr(sprintf('%o', fileperms($credentialsPath)), -4)
                ];

                // Verificar conteúdo do arquivo
                if (is_readable($credentialsPath)) {
                    $content = file_get_contents($credentialsPath);
                    $json = json_decode($content, true);
                    
                    $diagnostics['file_content'] = [
                        'valid_json' => json_last_error() === JSON_ERROR_NONE,
                        'json_error' => json_last_error_msg(),
                        'has_type' => isset($json['type']),
                        'has_project_id' => isset($json['project_id']),
                        'has_private_key_id' => isset($json['private_key_id']),
                        'has_private_key' => isset($json['private_key']),
                        'has_client_email' => isset($json['client_email']),
                        'file_project_id' => $json['project_id'] ?? 'NÃO ENCONTRADO',
                        'client_email' => $json['client_email'] ?? 'NÃO ENCONTRADO'
                    ];
                }
            } else {
                $diagnostics['file_info'] = [
                    'exists' => false,
                    'searched_paths' => [
                        $credentialsConfigPath,
                        storage_path('app/firebase/firebase-credentials.json'),
                        public_path('firebase-credentials.json')
                    ]
                ];
            }

            // Informações do servidor
            $diagnostics['server_info'] = [
                'php_version' => PHP_VERSION,
                'server_time' => date('Y-m-d H:i:s T'),
                'timezone' => date_default_timezone_get(),
                'environment' => app()->environment()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico Firebase',
                'diagnostics' => $diagnostics
            ]);

        } catch (\Throwable $th) {
            Log::error('Erro no diagnóstico Firebase: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro no diagnóstico: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Testa a conectividade Firebase sem enviar notificação
     */
    public function testConnection(Request $request)
    {
        try {
            $projectId = config('firebase.project_id');
            $credentialsPath = config('firebase.credentials');
            
            if (!file_exists($credentialsPath)) {
                $storagePath = storage_path('app/firebase/firebase-credentials.json');
                if (file_exists($storagePath)) {
                    $credentialsPath = $storagePath;
                } else {
                    $publicPath = public_path('firebase-credentials.json');
                    if (file_exists($publicPath)) {
                        $credentialsPath = $publicPath;
                    } else {
                        throw new Exception('Arquivo de credenciais Firebase não encontrado');
                    }
                }
            }

            Log::info('Testando conectividade Firebase', [
                'credentials_path' => $credentialsPath,
                'project_id' => $projectId,
                'server_time' => date('c'),
                'utc_time' => gmdate('c')
            ]);

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            // Tenta inicializar o messaging (isso valida as credenciais)
            $messaging = $factory->createMessaging();
            
            // Se chegou até aqui, as credenciais estão OK
            Log::info('Conectividade Firebase OK - credenciais válidas');
            
            return response()->json([
                'success' => true,
                'message' => 'Conectividade Firebase testada com sucesso',
                'data' => [
                    'project_id' => $projectId,
                    'credentials_valid' => true,
                    'server_time' => date('c'),
                    'utc_time' => gmdate('c')
                ]
            ]);

        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            Log::error('Erro de autenticação Firebase no teste: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'server_time' => date('c'),
                'utc_time' => gmdate('c')
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro de autenticação Firebase: ' . $e->getMessage(),
                'debug_info' => [
                    'server_time' => date('c'),
                    'utc_time' => gmdate('c'),
                    'suggestion' => $e->getMessage() === 'invalid_grant' 
                        ? 'Verificar sincronização de horário ou regenerar credenciais'
                        : 'Verificar configurações Firebase'
                ]
            ], 401);
        } catch (\Throwable $th) {
            Log::error('Erro no teste de conectividade Firebase: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro no teste: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Envia notificação Firebase para um tópico
     */
    public function sendToTopic(Request $request)
    {
        try {
            // Validação dos dados de entrada
            $request->validate([
                'topic' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:1000',
                'data' => 'nullable|array',
            ]);

            // Debug das configurações Firebase
            $projectId = config('firebase.project_id');
            Log::info('Firebase Project ID configurado: ' . $projectId);
            
            if (empty($projectId)) {
                throw new Exception('Project ID do Firebase não configurado');
            }

            // Verifica se o arquivo de credenciais existe
            $credentialsPath = config('firebase.credentials');
            Log::info('Caminho inicial das credenciais: ' . $credentialsPath);
            
            // Se o caminho não for absoluto, tenta diferentes localizações
            if (!file_exists($credentialsPath)) {
                // Tenta na pasta storage/app/firebase/
                $storagePath = storage_path('app/firebase/firebase-credentials.json');
                if (file_exists($storagePath)) {
                    $credentialsPath = $storagePath;
                } else {
                    // Tenta na pasta public/
                    $publicPath = public_path('firebase-credentials.json');
                    if (file_exists($publicPath)) {
                        $credentialsPath = $publicPath;
                    } else {
                        throw new Exception('Arquivo de credenciais Firebase não encontrado');
                    }
                }
            }

            // Verifica se o arquivo é legível
            if (!is_readable($credentialsPath)) {
                throw new Exception('Arquivo de credenciais Firebase não pode ser lido. Verifique as permissões.');
            }

            // Verifica se o arquivo é um JSON válido
            $credentialsContent = file_get_contents($credentialsPath);
            $credentialsJson = json_decode($credentialsContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Arquivo de credenciais Firebase contém JSON inválido: ' . json_last_error_msg());
            }

            // Verifica se contém as chaves necessárias
            $requiredKeys = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
            foreach ($requiredKeys as $key) {
                if (!isset($credentialsJson[$key])) {
                    throw new Exception("Arquivo de credenciais Firebase não contém a chave necessária: {$key}");
                }
            }

            Log::info('Usando credenciais Firebase do caminho: ' . $credentialsPath, [
                'file_size' => filesize($credentialsPath),
                'project_id_from_file' => $credentialsJson['project_id'] ?? 'não encontrado',
                'client_email' => $credentialsJson['client_email'] ?? 'não encontrado',
            ]);

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            $messaging = $factory->createMessaging();

            $topic = $request->topic;
            $title = $request->title;
            $body = $request->body;
            $data = $request->data ?? [];

            // Cria a mensagem para o tópico
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body));

            // Adiciona dados personalizados se fornecidos
            if (!empty($data)) {
                $message = $message->withData($data);
            }

            $messaging->send($message);

            Log::info('Notificação Firebase enviada com sucesso para o tópico', [
                'topic' => $topic,
                'title' => $title,
                'project_id' => $projectId
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Notificação enviada com sucesso para o tópico',
                'data' => [
                    'topic' => $topic,
                    'title' => $title,
                    'body' => $body
                ]
            ]);

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            $errorMessage = $e->getMessage();
            
            // Tratamento específico para invalid_grant
            if (strpos($errorMessage, 'invalid_grant') !== false) {
                Log::error('Erro Firebase - invalid_grant detectado', [
                    'error_message' => $errorMessage,
                    'server_time' => date('c'),
                    'server_timezone' => date_default_timezone_get(),
                    'utc_time' => gmdate('c'),
                ]);
                
                return response()->json([
                    'success' => false, 
                    'error' => 'Erro de autenticação Firebase (invalid_grant). Possíveis causas: horário do servidor dessincronizado ou credenciais inválidas.',
                    'debug_info' => [
                        'server_time' => date('c'),
                        'utc_time' => gmdate('c'),
                    ]
                ], 401);
            }
            
            Log::error('Erro Firebase - Mensagem inválida para tópico: ' . $errorMessage, [
                'error_code' => $e->getCode(),
                'project_id' => config('firebase.project_id'),
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Mensagem inválida: ' . $errorMessage
            ], 400);
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            Log::error('Erro Firebase - Autenticação ao enviar para tópico: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'project_id' => config('firebase.project_id'),
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Erro de autenticação Firebase: ' . $e->getMessage()
            ], 401);
        } catch (\Throwable $th) {
            Log::error('Erro ao enviar notificação Firebase para tópico: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
                'project_id' => config('firebase.project_id'),
            ]);
            return response()->json([
                'success' => false, 
                'error' => 'Erro interno: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todas as notificações do usuário autenticado
     */
    // public function index(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $request->user();
    //         $onlyUnread = $request->boolean('only_unread', false);
    //         $limit = (int) $request->get('limit', 50);

    //         $notifications = $this->localNotificationService->getUserNotifications($user, $onlyUnread, $limit);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Notificações listadas com sucesso.',
    //             'data' => $notifications,
    //             'total' => $notifications->count(),
    //             'unread_count' => $this->localNotificationService->getUnreadCount($user),
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao listar notificações.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Conta notificações não lidas
    //  */
    // public function unreadCount(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $request->user();
    //         $count = $this->localNotificationService->getUnreadCount($user);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Contagem de notificações não lidas.',
    //             'data' => [
    //                 'unread_count' => $count
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao contar notificações não lidas.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Marca uma notificação específica como lida
    //  */
    // public function markAsRead(Request $request, string $notificationId): JsonResponse
    // {
    //     try {
    //         $user = $request->user();
    //         $success = $this->localNotificationService->markAsRead($user, $notificationId);

    //         if (!$success) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Notificação não encontrada.',
    //             ], 404);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Notificação marcada como lida.',
    //             'data' => [
    //                 'unread_count' => $this->localNotificationService->getUnreadCount($user)
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao marcar notificação como lida.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Marca todas as notificações como lidas
    //  */
    // public function markAllAsRead(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $request->user();
    //         $success = $this->localNotificationService->markAllAsRead($user);

    //         return response()->json([
    //             'success' => $success,
    //             'message' => $success 
    //                 ? 'Todas as notificações foram marcadas como lidas.' 
    //                 : 'Erro ao marcar notificações como lidas.',
    //             'data' => [
    //                 'unread_count' => 0
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao marcar todas as notificações como lidas.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Envia uma notificação de teste para o usuário autenticado
    //  */
    // public function sendTestNotification(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $request->user();
            
    //         $success = $this->localNotificationService->sendToUser(
    //             $user,
    //             'Notificação de Teste',
    //             'Esta é uma notificação de teste para verificar se o sistema está funcionando corretamente.',
    //             'info',
    //             '/dashboard',
    //             'Ir ao Dashboard',
    //             ['type' => 'test', 'timestamp' => now()]
    //         );

    //         return response()->json([
    //             'success' => $success,
    //             'message' => $success 
    //                 ? 'Notificação de teste enviada com sucesso.' 
    //                 : 'Erro ao enviar notificação de teste.',
    //             'data' => [
    //                 'unread_count' => $this->localNotificationService->getUnreadCount($user)
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao enviar notificação de teste.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // /**
    //  * Envia uma notificação local personalizada
    //  */
    // public function sendLocalNotification(Request $request): JsonResponse
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'message' => 'required|string|max:1000',
    //         'type' => 'nullable|string|in:info,success,warning,error',
    //         'action_url' => 'nullable|url',
    //         'action_text' => 'nullable|string|max:100',
    //         'metadata' => 'nullable|array',
    //     ]);

    //     try {
    //         $user = $request->user();
            
    //         $success = $this->localNotificationService->sendToUser(
    //             $user,
    //             $request->title,
    //             $request->message,
    //             $request->type ?? 'info',
    //             $request->action_url,
    //             $request->action_text,
    //             $request->metadata
    //         );

    //         return response()->json([
    //             'success' => $success,
    //             'message' => $success 
    //                 ? 'Notificação enviada com sucesso.' 
    //                 : 'Erro ao enviar notificação.',
    //             'data' => [
    //                 'unread_count' => $this->localNotificationService->getUnreadCount($user)
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao enviar notificação.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
