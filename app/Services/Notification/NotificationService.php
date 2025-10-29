<?php

namespace App\Services\Notification;

use App\Mail\NewPublicationNotification;
use App\Models\Publication;
use App\Models\User;
use App\Models\University\University;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class NotificationService
{
    /**
     * Enviar notificações para usuários relacionados à universidade da publicação
     */
    public function notifyUsersAboutNewPublication(Publication $publication): void
    {
        try {
            // Buscar a universidade da publicação
            $university = University::find($publication->university_id);
            
            if (!$university) {
                Log::warning("Universidade não encontrada para publicação ID: {$publication->id}");
                return;
            }

            // Buscar usuários relacionados à universidade
            $users = $this->findRelatedUsers($university);
            
            if ($users->isEmpty()) {
                Log::info("Nenhum usuário encontrado para notificação da universidade: {$university->name}");
                return;
            }

            // Enviar emails em lote (usando queue para performance)
            $this->sendNotificationEmails($publication, $users, $university);
            
            Log::info("Notificações enviadas para {$users->count()} usuários sobre publicação: {$publication->title}");
            
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificações para publicação ID {$publication->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buscar usuários que têm relação com a universidade
     */
    private function findRelatedUsers(University $university): \Illuminate\Database\Eloquent\Collection
    {
        $universityName = $university->name;
        
        // Palavras-chave da universidade para busca mais flexível
        $keywords = $this->extractUniversityKeywords($universityName);
        
        $query = User::with('profile')
            ->whereHas('profile', function ($q) use ($keywords, $universityName) {
                // Busca exata no campo faculdade
                $q->where('faculdade', 'LIKE', "%{$universityName}%");
                
                // Busca por palavras-chave no campo bio
                foreach ($keywords as $keyword) {
                    $q->orWhere('bio', 'LIKE', "%{$keyword}%");
                }
                
                // Busca por palavras-chave no campo faculdade
                foreach ($keywords as $keyword) {
                    $q->orWhere('faculdade', 'LIKE', "%{$keyword}%");
                }
            })
            ->where('email', '!=', null) // Garantir que tem email
            ->whereNotNull('email');
            
        return $query->get();
    }

    /**
     * Extrair palavras-chave do nome da universidade
     */
    private function extractUniversityKeywords(string $universityName): array
    {
        // Palavras comuns que podem ser ignoradas
        $stopWords = ['de', 'da', 'do', 'das', 'dos', 'e', 'em', 'na', 'no', 'para', 'por'];
        
        // Dividir o nome em palavras
        $words = preg_split('/\s+/', strtolower($universityName));
        
        // Filtrar palavras pequenas e stop words
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) >= 3 && !in_array($word, $stopWords);
        });
        
        // Adicionar palavras-chave específicas baseadas no nome
        if (strpos(strtolower($universityName), 'católica') !== false) {
            $keywords[] = 'católica';
            $keywords[] = 'ucm';
        }
        
        if (strpos(strtolower($universityName), 'moçambique') !== false) {
            $keywords[] = 'moçambique';
            $keywords[] = 'mozambique';
        }
        
        return array_unique($keywords);
    }

    /**
     * Enviar emails de notificação
     */
    private function sendNotificationEmails(Publication $publication, $users, University $university): void
    {
        foreach ($users as $user) {
            try {
                // Usar queue para não bloquear a resposta
                // Mail::to('kelvenbragoa@hotmail.com')->send(new NewPublicationNotification($publication, $user, $university));
                Mail::to($user->email)->cc(['kelvenbragoa@hotmail.com','gerson.houane@gmail.com'])
                    ->queue(new NewPublicationNotification($publication, $user, $university));
                    
                Log::debug("Email enfileirado para: {$user->email} sobre publicação: {$publication->title}");
                
            } catch (Exception $e) {
                Log::error("Erro ao enviar email para {$user->email}: " . $e->getMessage());
                // Continuar com os outros usuários mesmo se um falhar
            }
        }
    }

    /**
     * Obter estatísticas de notificações enviadas
     */
    public function getNotificationStats(Publication $publication): array
    {
        $university = University::find($publication->university_id);
        
        if (!$university) {
            return [
                'total_users' => 0,
                'university' => null,
                'keywords' => []
            ];
        }
        
        $users = $this->findRelatedUsers($university);
        $keywords = $this->extractUniversityKeywords($university->name);
        
        return [
            'total_users' => $users->count(),
            'university' => $university->name,
            'keywords' => $keywords,
            'users_preview' => $users->take(5)->pluck('name', 'email')->toArray()
        ];
    }

    /**
     * Testar sistema de notificação (apenas para desenvolvimento)
     */
    public function testNotification(Publication $publication, string $testEmail): bool
    {
        try {
            $university = University::find($publication->university_id);
            
            if (!$university) {
                throw new Exception('Universidade não encontrada');
            }
            
            // Criar usuário de teste
            $testUser = new User([
                'name' => 'Usuário de Teste',
                'email' => $testEmail
            ]);
            
            Mail::to($testEmail)->send(new NewPublicationNotification($publication, $testUser, $university));
            
            Log::info("Email de teste enviado para: {$testEmail}");
            
            return true;
            
        } catch (Exception $e) {
            Log::error("Erro no teste de notificação: " . $e->getMessage());
            throw $e;
        }
    }
}