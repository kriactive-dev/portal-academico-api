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

            // Verifica se o arquivo de credenciais existe
            $credentialsPath = config('firebase.credentials');
            
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

            Log::info('Usando credenciais Firebase do caminho: ' . $credentialsPath);

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId(config('firebase.project_id'));

            $messaging = $factory->createMessaging();

            $token = $request->token;
            $title = $request->title;
            $body = $request->body;

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body));

            $messaging->send($message);

            Log::info('Notificação Firebase enviada com sucesso', [
                'token' => substr($token, 0, 20) . '...', // Log parcial do token por segurança
                'title' => $title
            ]);

            return response()->json(['success' => true, 'message' => 'Notificação enviada com sucesso']);

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::error('Erro Firebase - Mensagem inválida: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Mensagem inválida: ' . $e->getMessage()
            ], 400);
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            Log::error('Erro Firebase - Autenticação: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Erro de autenticação Firebase: ' . $e->getMessage()
            ], 401);
        } catch (\Throwable $th) {
            Log::error('Erro ao enviar notificação Firebase: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
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
