<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Notifications\LocalNotification as LocalNotificationClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

class LocalNotificationService
{
    /**
     * Envia notificação local para um usuário
     */
    public function sendToUser(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $metadata = null
    ): bool {
        try {
            $notification = new LocalNotificationClass(
                $title,
                $message,
                $type,
                $actionUrl,
                $actionText,
                $metadata
            );

            $user->notify($notification);
            
            Log::info("Notificação enviada para usuário {$user->id}: {$title}");
            return true;
            
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificação para usuário {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia notificação local para múltiplos usuários
     */
    public function sendToUsers(
        $users,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $metadata = null
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => is_countable($users) ? count($users) : $users->count(),
        ];

        $notification = new LocalNotificationClass(
            $title,
            $message,
            $type,
            $actionUrl,
            $actionText,
            $metadata
        );

        foreach ($users as $user) {
            try {
                $user->notify($notification);
                $results['success']++;
                
                Log::debug("Notificação enviada para usuário {$user->id}: {$title}");
                
            } catch (Exception $e) {
                $results['failed']++;
                Log::error("Erro ao enviar notificação para usuário {$user->id}: " . $e->getMessage());
            }
        }

        Log::info("Notificações em lote enviadas: {$results['success']} sucessos, {$results['failed']} falhas");
        
        return $results;
    }

    /**
     * Envia notificação usando Notification facade (mais eficiente para múltiplos usuários)
     */
    public function sendBulkNotification(
        $users,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?array $metadata = null
    ): bool {
        try {
            $notification = new LocalNotificationClass(
                $title,
                $message,
                $type,
                $actionUrl,
                $actionText,
                $metadata
            );

            Notification::send($users, $notification);
            
            $count = is_countable($users) ? count($users) : $users->count();
            Log::info("Notificação em lote enviada para {$count} usuários: {$title}");
            
            return true;
            
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificação em lote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia notificação de boas-vindas
     */
    public function sendWelcomeNotification(User $user): bool
    {
        return $this->sendToUser(
            $user,
            'Bem-vindo ao Sistema Acadêmico UCM!',
            'Sua conta foi criada com sucesso. Explore todas as funcionalidades disponíveis.',
            'success',
            '/dashboard',
            'Ir ao Dashboard',
            ['type' => 'welcome', 'user_id' => $user->id]
        );
    }

    /**
     * Envia notificação de nova publicação
     */
    public function sendPublicationNotification(
        $users,
        string $publicationTitle,
        string $universityName,
        int $publicationId
    ): array {
        return $this->sendToUsers(
            $users,
            'Nova Publicação Disponível',
            "Uma nova publicação '{$publicationTitle}' foi publicada pela {$universityName}.",
            'info',
            "/publications/{$publicationId}",
            'Ver Publicação',
            [
                'type' => 'publication',
                'publication_id' => $publicationId,
                'university' => $universityName
            ]
        );
    }

    /**
     * Envia notificação de documento aprovado
     */
    public function sendDocumentApprovedNotification(User $user, string $documentName, int $documentId): bool
    {
        return $this->sendToUser(
            $user,
            'Documento Aprovado',
            "Seu documento '{$documentName}' foi aprovado e está pronto para download.",
            'success',
            "/documents/{$documentId}",
            'Ver Documento',
            ['type' => 'document_approved', 'document_id' => $documentId]
        );
    }

    /**
     * Envia notificação de documento rejeitado
     */
    public function sendDocumentRejectedNotification(User $user, string $documentName, string $reason): bool
    {
        return $this->sendToUser(
            $user,
            'Documento Rejeitado',
            "Seu documento '{$documentName}' foi rejeitado. Motivo: {$reason}",
            'warning',
            '/documents',
            'Ver Documentos',
            ['type' => 'document_rejected', 'reason' => $reason]
        );
    }

    /**
     * Envia notificação de livro reservado
     */
    public function sendBookReservedNotification(User $user, string $bookTitle, string $libraryName): bool
    {
        return $this->sendToUser(
            $user,
            'Livro Reservado',
            "O livro '{$bookTitle}' foi reservado para você na biblioteca {$libraryName}.",
            'info',
            '/library/my-reservations',
            'Ver Reservas',
            ['type' => 'book_reserved', 'library' => $libraryName]
        );
    }

    /**
     * Envia notificação de lembrete de devolução de livro
     */
    public function sendBookReturnReminderNotification(User $user, string $bookTitle, string $dueDate): bool
    {
        return $this->sendToUser(
            $user,
            'Lembrete: Devolução de Livro',
            "Lembre-se de devolver o livro '{$bookTitle}' até {$dueDate}.",
            'warning',
            '/library/my-loans',
            'Ver Empréstimos',
            ['type' => 'book_return_reminder', 'due_date' => $dueDate]
        );
    }

    /**
     * Marca todas as notificações de um usuário como lidas
     */
    public function markAllAsRead(User $user): bool
    {
        try {
            $user->unreadNotifications->markAsRead();
            Log::info("Todas as notificações do usuário {$user->id} foram marcadas como lidas");
            return true;
        } catch (Exception $e) {
            Log::error("Erro ao marcar notificações como lidas para usuário {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca uma notificação específica como lida
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        try {
            $notification = $user->unreadNotifications->where('id', $notificationId)->first();
            if ($notification) {
                $notification->markAsRead();
                Log::info("Notificação {$notificationId} marcada como lida para usuário {$user->id}");
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error("Erro ao marcar notificação {$notificationId} como lida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém todas as notificações de um usuário
     */
    public function getUserNotifications(User $user, bool $onlyUnread = false, int $limit = 50)
    {
        try {
            if ($onlyUnread) {
                return $user->unreadNotifications()
                    ->limit($limit)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return $user->notifications()
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Exception $e) {
            Log::error("Erro ao buscar notificações do usuário {$user->id}: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Conta notificações não lidas de um usuário
     */
    public function getUnreadCount(User $user): int
    {
        try {
            return $user->unreadNotifications()->count();
        } catch (Exception $e) {
            Log::error("Erro ao contar notificações não lidas do usuário {$user->id}: " . $e->getMessage());
            return 0;
        }
    }
}
