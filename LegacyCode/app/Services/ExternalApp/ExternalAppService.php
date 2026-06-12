<?php

namespace App\Services\ExternalApp;

use App\Models\ExternalApp\ExternalApp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class ExternalAppService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Listar cursos com filtros e paginação
     */
    public function listExternalApps(array $filters = []): LengthAwarePaginator
    {
        $query = ExternalApp::orderBy('name', 'asc');

        // Aplicar filtros
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * Buscar curso por ID
     */
    public function findExternalApp(int $id): ?ExternalApp
    {
        return ExternalApp::with(['creator', 'updater', 'deleter'])->find($id);
    }

    /**
     * Criar novo curso
     */
    public function createExternalApp(array $data): ExternalApp
    {
        try {
            DB::beginTransaction();

            $externalApp = ExternalApp::create($data);

            DB::commit();

            return $externalApp->fresh(['creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualizar curso
     */
    public function updateExternalApp(ExternalApp $externalApp, array $data): ExternalApp
    {
        try {
            DB::beginTransaction();

            $externalApp->update($data);

            DB::commit();

            return $externalApp->fresh(['creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar curso (soft delete)
     */
    public function deleteExternalApp(ExternalApp $externalApp): bool
    {
        return $externalApp->delete();
    }

    /**
     * Restaurar curso deletado
     */
    public function restoreExternalApp(int $id): ?ExternalApp
    {
        $externalApp = ExternalApp::withTrashed()->find($id);
        
        if ($externalApp && $externalApp->trashed()) {
            $externalApp->restore();
            return $externalApp->fresh(['university', 'creator', 'updater']);
        }

        return null;
    }

    /**
     * Deletar curso permanentemente
     */
    public function forceDeleteExternalApp(int $id): bool
    {
        $externalApp = ExternalApp::withTrashed()->find($id);
        
        if ($externalApp) {
            return $externalApp->forceDelete();
        }

        return false;
    }

    /**
     * Obter estatísticas dos cursos
     */
    public function getStatistics(): array
    {
        $total = ExternalApp::count();
        $totalWithTrashed = ExternalApp::withTrashed()->count();
        $deleted = $totalWithTrashed - $total;
       

        return [
            'total' => $total,
            'deleted' => $deleted,
            'total_with_trashed' => $totalWithTrashed
        ];
    }

    /**
     * Buscar cursos por universidade
     */

    /**
     * Buscar cursos por termo
     */
    public function searchExternalApps(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return ExternalApp::with([ 'creator', 'updater'])
            ->search($term)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }


    /**
     * Duplicar curso
     */
    public function duplicateExternalApp(ExternalApp $externalApp): ExternalApp
    {
        $data = $externalApp->toArray();
        
        // Remover campos que não devem ser duplicados
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        unset($data['created_by_user_id'], $data['updated_by_user_id'], $data['deleted_by_user_id']);
        
        // Adicionar sufixo ao nome e código
        $data['name'] = $data['name'] . ' (Cópia)';
        $data['description'] = $data['description'] . ' (Cópia)';
        $data['url'] = $data['url'];
        

        return ExternalApp::create($data);
    }

}
