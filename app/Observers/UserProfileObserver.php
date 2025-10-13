<?php

namespace App\Observers;

use App\Models\UserProfile;
use App\Services\User\UserProfileEnrichmentService;
use Illuminate\Support\Facades\Log;

class UserProfileObserver
{
    protected UserProfileEnrichmentService $enrichmentService;

    public function __construct(UserProfileEnrichmentService $enrichmentService)
    {
        $this->enrichmentService = $enrichmentService;
    }

    /**
     * Handle the UserProfile "created" event.
     */
    public function created(UserProfile $userProfile): void
    {
        // Verificar se o enriquecimento está habilitado
        if (!$this->enrichmentService->isEnrichmentEnabled()) {
            Log::debug('UserProfile enrichment is disabled', [
                'user_profile_id' => $userProfile->id
            ]);
            return;
        }

        Log::info('UserProfile created, starting enrichment process', [
            'user_profile_id' => $userProfile->id,
            'user_id' => $userProfile->user_id
        ]);

        // Executar o enriquecimento de forma assíncrona para não bloquear a criação
        try {
            // Fazer a consulta e atualização
            $this->enrichmentService->enrichProfile($userProfile);
        } catch (\Exception $e) {
            // Log do erro mas não falhar a criação do perfil
            Log::error('UserProfile enrichment failed in observer', [
                'user_profile_id' => $userProfile->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the UserProfile "updated" event.
     */
    public function updated(UserProfile $userProfile): void
    {
        // Verificar se o nome do usuário foi alterado
        if ($userProfile->user && $userProfile->user->wasChanged('name')) {
            
            if (!$this->enrichmentService->isEnrichmentEnabled()) {
                return;
            }

            Log::info('User name changed, re-enriching profile', [
                'user_profile_id' => $userProfile->id,
                'user_id' => $userProfile->user_id,
                'new_name' => $userProfile->user->name
            ]);

            try {
                $this->enrichmentService->enrichProfile($userProfile);
            } catch (\Exception $e) {
                Log::error('UserProfile re-enrichment failed after name change', [
                    'user_profile_id' => $userProfile->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the UserProfile "saving" event.
     * Executado antes de salvar (created e updated)
     */
    public function saving(UserProfile $userProfile): void
    {
        // Log de debug para acompanhar as operações
        Log::debug('UserProfile saving', [
            'user_profile_id' => $userProfile->id ?? 'new',
            'user_id' => $userProfile->user_id,
            'is_new' => !$userProfile->exists
        ]);
    }

    /**
     * Handle the UserProfile "saved" event.
     * Executado após salvar (created e updated)
     */
    public function saved(UserProfile $userProfile): void
    {
        Log::debug('UserProfile saved successfully', [
            'user_profile_id' => $userProfile->id,
            'user_id' => $userProfile->user_id
        ]);
    }

    /**
     * Handle the UserProfile "deleting" event.
     */
    public function deleting(UserProfile $userProfile): void
    {
        Log::info('UserProfile being deleted', [
            'user_profile_id' => $userProfile->id,
            'user_id' => $userProfile->user_id
        ]);
    }

    /**
     * Handle the UserProfile "deleted" event.
     */
    public function deleted(UserProfile $userProfile): void
    {
        Log::info('UserProfile deleted', [
            'user_profile_id' => $userProfile->id,
            'user_id' => $userProfile->user_id
        ]);
    }

    /**
     * Handle the UserProfile "restored" event.
     */
    public function restored(UserProfile $userProfile): void
    {
        Log::info('UserProfile restored', [
            'user_profile_id' => $userProfile->id,
            'user_id' => $userProfile->user_id
        ]);

        // Re-enriquecer o perfil quando restaurado
        if ($this->enrichmentService->isEnrichmentEnabled()) {
            try {
                $this->enrichmentService->enrichProfile($userProfile);
            } catch (\Exception $e) {
                Log::error('UserProfile enrichment failed after restore', [
                    'user_profile_id' => $userProfile->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}