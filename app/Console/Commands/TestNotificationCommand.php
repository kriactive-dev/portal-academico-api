<?php

namespace App\Console\Commands;

use App\Models\Publication;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;

class TestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test {publication_id} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar sistema de notificação para uma publicação específica';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publicationId = $this->argument('publication_id');
        $email = $this->argument('email');

        $this->info("🧪 Testando sistema de notificação...");

        // Buscar a publicação
        $publication = Publication::find($publicationId);

        if (!$publication) {
            $this->error("❌ Publicação com ID {$publicationId} não encontrada.");
            return 1;
        }

        $this->info("📄 Publicação encontrada: {$publication->title}");

        // Obter estatísticas
        $stats = $this->notificationService->getNotificationStats($publication);

        $this->info("📊 Estatísticas de notificação:");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Universidade', $stats['university'] ?? 'N/A'],
                ['Total de usuários encontrados', $stats['total_users']],
                ['Palavras-chave usadas', implode(', ', $stats['keywords'])],
            ]
        );

        if ($stats['total_users'] > 0) {
            $this->info("👥 Pré-visualização de usuários:");
            foreach ($stats['users_preview'] as $userEmail => $userName) {
                $this->line("  • {$userName} ({$userEmail})");
            }
        }

        // Testar envio
        $confirm = $this->confirm("✉️ Deseja enviar um email de teste para {$email}?");

        if ($confirm) {
            try {
                $success = $this->notificationService->testNotification($publication, $email);

                if ($success) {
                    $this->info("✅ Email de teste enviado com sucesso para {$email}!");
                } else {
                    $this->error("❌ Falha ao enviar email de teste.");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro: " . $e->getMessage());
                return 1;
            }
        }

        // Opção para enviar para todos os usuários
        if ($stats['total_users'] > 0) {
            $sendToAll = $this->confirm("📢 Deseja enviar notificações para todos os {$stats['total_users']} usuários encontrados?");

            if ($sendToAll) {
                try {
                    $this->notificationService->notifyUsersAboutNewPublication($publication);
                    $this->info("✅ Notificações enviadas para todos os usuários!");
                } catch (\Exception $e) {
                    $this->error("❌ Erro: " . $e->getMessage());
                    return 1;
                }
            }
        }

        $this->info("🎉 Teste concluído!");
        return 0;
    }
}
