<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserProfile;
use App\Services\User\UserProfileEnrichmentService;
use Illuminate\Database\Eloquent\Collection;

class EnrichUserProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-profile:enrich 
                            {--id= : ID específico do perfil para enriquecer}
                            {--all : Enriquecer todos os perfis}
                            {--empty : Apenas perfis com campos vazios}
                            {--limit=50 : Limite de perfis para processar}
                            {--force : Forçar atualização mesmo com dados existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enriquecer perfis de usuários com dados de endpoints externos';

    protected UserProfileEnrichmentService $enrichmentService;

    /**
     * Create a new command instance.
     */
    public function __construct(UserProfileEnrichmentService $enrichmentService)
    {
        parent::__construct();
        $this->enrichmentService = $enrichmentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando enriquecimento de perfis de usuários...');

        // Verificar se o serviço está habilitado
        if (!$this->enrichmentService->isEnrichmentEnabled()) {
            $this->error('❌ Serviço de enriquecimento está desabilitado!');
            $this->info('💡 Habilite definindo USER_ENRICHMENT_ENABLED=true no .env');
            return self::FAILURE;
        }

        // Processar por ID específico
        if ($profileId = $this->option('id')) {
            return $this->enrichSpecificProfile($profileId);
        }

        // Processar múltiplos perfis
        return $this->enrichMultipleProfiles();
    }

    /**
     * Enriquecer um perfil específico
     */
    private function enrichSpecificProfile(int $profileId): int
    {
        $this->info("🔍 Buscando perfil ID: {$profileId}");

        $userProfile = UserProfile::with('user')->find($profileId);

        if (!$userProfile) {
            $this->error("❌ Perfil com ID {$profileId} não encontrado!");
            return self::FAILURE;
        }

        $this->info("👤 Usuário: {$userProfile->user->name} ({$userProfile->user->email})");

        if ($this->enrichmentService->enrichProfile($userProfile)) {
            $this->info("✅ Perfil enriquecido com sucesso!");
            return self::SUCCESS;
        } else {
            $this->error("❌ Falha ao enriquecer o perfil!");
            return self::FAILURE;
        }
    }

    /**
     * Enriquecer múltiplos perfis
     */
    private function enrichMultipleProfiles(): int
    {
        $query = UserProfile::with('user');

        // Filtrar apenas perfis com campos vazios
        if ($this->option('empty')) {
            $query->where(function ($q) {
                $q->whereNull('phone')
                  ->orWhereNull('address')
                  ->orWhereNull('birth_date')
                  ->orWhereNull('document')
                  ->orWhere('phone', '')
                  ->orWhere('address', '')
                  ->orWhere('document', '');
            });
        }

        // Processar todos ou com limite
        if ($this->option('all')) {
            $profiles = $query->get();
        } else {
            $limit = (int) $this->option('limit');
            $profiles = $query->limit($limit)->get();
        }

        $totalProfiles = $profiles->count();

        if ($totalProfiles === 0) {
            $this->info('🤷‍♂️ Nenhum perfil encontrado para processar.');
            return self::SUCCESS;
        }

        $this->info("📊 Encontrados {$totalProfiles} perfis para processar");

        $progressBar = $this->output->createProgressBar($totalProfiles);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($profiles as $profile) {
            try {
                $userName = $profile->user->name ?? 'N/A';
                
                // Verificar se deve pular perfis já preenchidos
                if (!$this->option('force') && $this->isProfileAlreadyEnriched($profile)) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                if ($this->enrichmentService->enrichProfile($profile)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("\n❌ Erro processando perfil ID {$profile->id}: " . $e->getMessage());
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Relatório final
        $this->info('📈 Relatório de Enriquecimento:');
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Total processados', $totalProfiles],
                ['✅ Sucessos', $successCount],
                ['❌ Erros', $errorCount],
                ['⏭️ Pulados', $skippedCount],
                ['📊 Taxa de sucesso', $totalProfiles > 0 ? round(($successCount / $totalProfiles) * 100, 2) . '%' : '0%']
            ]
        );

        return $errorCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Verificar se o perfil já está enriquecido
     */
    private function isProfileAlreadyEnriched(UserProfile $profile): bool
    {
        $fieldsToCheck = ['phone', 'address', 'document', 'city', 'state'];
        
        foreach ($fieldsToCheck as $field) {
            if (!empty($profile->{$field})) {
                return true; // Se pelo menos um campo está preenchido
            }
        }
        
        return false;
    }
}
