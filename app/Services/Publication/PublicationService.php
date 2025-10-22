<?php

namespace App\Services\Publication;

use App\Models\Publication;
use App\Models\University\University;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class PublicationService
{
    /**
     * Listar publicações com filtros e paginação
     */
    public function listUniversities(array $filters = []): LengthAwarePaginator
    {
        $query = University::orderBy('name', 'asc');

        // Aplicar filtros se necessário
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }
    public function listPublications(array $filters = []): LengthAwarePaginator
    {
        $query = Publication::with(['creator', 'updater'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['title']) && !empty($filters['title'])) {
            $query->byTitle($filters['title']);
        }

        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->active();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringIn(7);
                    break;
            }
        }

        if (isset($filters['has_file'])) {
            if ($filters['has_file']) {
                $query->whereNotNull('file_path');
            } else {
                $query->whereNull('file_path');
            }
        }

        if (isset($filters['created_from']) && !empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to']) && !empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        if (isset($filters['expires_from']) && !empty($filters['expires_from'])) {
            $query->where('expires_at', '>=', $filters['expires_from']);
        }

        if (isset($filters['expires_to']) && !empty($filters['expires_to'])) {
            $query->where('expires_at', '<=', $filters['expires_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * Buscar publicação por ID
     */
    public function findPublication(int $id): ?Publication
    {
        return Publication::with(['creator', 'updater', 'deleter'])->find($id);
    }

    /**
     * Criar nova publicação
     */
    public function createPublication(array $data, ?UploadedFile $file = null): Publication
    {
        try {
            DB::beginTransaction();

            // Processar upload de arquivo se fornecido
            if ($file) {
                $data['file_path'] = $this->storeFile($file);
            }

            $publication = Publication::create($data);

            DB::commit();

            return $publication->fresh(['creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();

            // Limpar arquivo se foi feito upload
            if (isset($data['file_path']) && Storage::exists($data['file_path'])) {
                Storage::delete($data['file_path']);
            }

            throw $e;
        }
    }

    /**
     * Atualizar publicação
     */
    public function updatePublication(Publication $publication, array $data, ?UploadedFile $file = null): Publication
    {
        try {
            DB::beginTransaction();

            $oldFilePath = $publication->file_path;

            // Processar novo arquivo se fornecido
            if ($file) {
                $data['file_path'] = $this->storeFile($file);
            }

            $publication->update($data);

            // Deletar arquivo antigo se novo arquivo foi fornecido
            if ($file && $oldFilePath && Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }

            DB::commit();

            return $publication->fresh(['creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();

            // Limpar novo arquivo se upload falhou
            if (isset($data['file_path']) && $data['file_path'] !== $oldFilePath && Storage::exists($data['file_path'])) {
                Storage::delete($data['file_path']);
            }

            throw $e;
        }
    }

    /**
     * Deletar publicação
     */
    public function deletePublication(Publication $publication): bool
    {
        return $publication->delete();
    }

    /**
     * Restaurar publicação deletada
     */
    public function restorePublication(int $id): ?Publication
    {
        $publication = Publication::withTrashed()->find($id);
        
        if ($publication && $publication->trashed()) {
            $publication->restore();
            return $publication->fresh(['creator', 'updater']);
        }

        return null;
    }

    /**
     * Deletar publicação permanentemente
     */
    public function forceDeletePublication(int $id): bool
    {
        $publication = Publication::withTrashed()->find($id);
        
        if ($publication) {
            return $publication->forceDelete();
        }

        return false;
    }

    /**
     * Fazer upload de arquivo
     */
    public function uploadFile(Publication $publication, UploadedFile $file): Publication
    {
        try {
            DB::beginTransaction();

            $oldFilePath = $publication->file_path;
            $newFilePath = $this->storeFile($file);

            $publication->update(['file_path' => $newFilePath]);

            // Deletar arquivo antigo
            if ($oldFilePath && Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }

            DB::commit();

            return $publication->fresh(['creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();

            // Limpar novo arquivo se upload falhou
            if (isset($newFilePath) && Storage::exists($newFilePath)) {
                Storage::delete($newFilePath);
            }

            throw $e;
        }
    }

    /**
     * Remover arquivo da publicação
     */
    public function removeFile(Publication $publication): Publication
    {
        if ($publication->hasFile()) {
            $publication->deleteFile();
            $publication->update(['file_path' => null]);
        }

        return $publication->fresh(['creator', 'updater']);
    }

    /**
     * Obter estatísticas das publicações
     */
    public function getStatistics(): array
    {
        $total = Publication::count();
        $active = Publication::active()->count();
        $expired = Publication::expired()->count();
        $expiringSoon = Publication::expiringIn(7)->count();
        $withFiles = Publication::whereNotNull('file_path')->count();
        $withoutFiles = Publication::whereNull('file_path')->count();

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'expiring_soon' => $expiringSoon,
            'with_files' => $withFiles,
            'without_files' => $withoutFiles,
            'percentage_active' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
            'percentage_expired' => $total > 0 ? round(($expired / $total) * 100, 2) : 0,
            'percentage_with_files' => $total > 0 ? round(($withFiles / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Buscar publicações por status
     */
    public function getPublicationsByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        $query = Publication::with(['creator', 'updater']);

        switch ($status) {
            case 'active':
                $query->active();
                break;
            case 'expired':
                $query->expired();
                break;
            case 'expiring_soon':
                $query->expiringIn(7);
                break;
            default:
                throw new \InvalidArgumentException('Status inválido: ' . $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Buscar publicações por termo
     */
    public function searchPublications(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Publication::with(['creator', 'updater'])
            ->search($term)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obter publicações ativas (não expiradas)
     */
    public function getActivePublications(int $perPage = 15): LengthAwarePaginator
    {
        return Publication::with(['creator', 'updater'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obter publicações que expiram em X dias
     */
    public function getPublicationsExpiringIn(int $days, int $perPage = 15): LengthAwarePaginator
    {
        return Publication::with(['creator', 'updater'])
            ->expiringIn($days)
            ->orderBy('expires_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Limpar publicações expiradas há mais de X dias
     */
    public function cleanupExpiredPublications(int $daysAfterExpiration = 30): int
    {
        $cutoffDate = now()->subDays($daysAfterExpiration);
        
        $expiredPublications = Publication::where('expires_at', '<', $cutoffDate)->get();
        
        $deletedCount = 0;
        
        foreach ($expiredPublications as $publication) {
            if ($publication->forceDelete()) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Duplicar publicação
     */
    public function duplicatePublication(Publication $publication): Publication
    {
        $data = $publication->toArray();
        
        // Remover campos que não devem ser duplicados
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        unset($data['created_by_user_id'], $data['updated_by_user_id'], $data['deleted_by_user_id']);
        
        // Adicionar sufixo ao título
        $data['title'] = $data['title'] . ' (Cópia)';
        
        // Copiar arquivo se existir
        if ($publication->hasFile()) {
            $data['file_path'] = $this->duplicateFile($publication->file_path);
        }

        return Publication::create($data);
    }

    /**
     * Armazenar arquivo
     */
    private function storeFile(UploadedFile $file): string
    {
        // Validar tipo de arquivo
        $allowedMimes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file->getClientOriginalExtension(), $allowedMimes)) {
            throw new \InvalidArgumentException('Tipo de arquivo não permitido: ' . $file->getClientOriginalExtension());
        }

        // Validar tamanho (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('Arquivo muito grande. Máximo permitido: 10MB');
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        
        return $file->storeAs('publications', $fileName, 'public');
    }

    /**
     * Duplicar arquivo
     */
    private function duplicateFile(string $originalPath): string
    {
        if (!Storage::exists($originalPath)) {
            throw new \Exception('Arquivo original não encontrado: ' . $originalPath);
        }

        $pathInfo = pathinfo($originalPath);
        $newFileName = $pathInfo['filename'] . '_copy_' . time() . '.' . $pathInfo['extension'];
        $newPath = $pathInfo['dirname'] . '/' . $newFileName;

        Storage::copy($originalPath, $newPath);

        return $newPath;
    }
}