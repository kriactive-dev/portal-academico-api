<?php

namespace App\Services\Document;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentFile;
use App\Models\Documents\DocumentStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Listar documentos com filtros
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = Document::with(['user', 'documentStatus', 'documentFiles', 'createdByUser']);

        // Filtro por busca
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('comments', 'like', "%{$search}%");
            });
        }

        // Filtro por status
        if (isset($filters['status_id'])) {
            $query->byStatus($filters['status_id']);
        }

        // Filtro por usuário
        if (isset($filters['created_by_user_id'])) {
            $query->byUser($filters['created_by_user_id']);
        }

        // Filtro por tipo de arquivo
        if (isset($filters['file_type'])) {
            $query->byFileType($filters['file_type']);
        }

        // Filtro por data de vencimento
        if (isset($filters['due_date_filter'])) {
            switch ($filters['due_date_filter']) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'due_soon':
                    $days = $filters['due_days'] ?? 7;
                    $query->dueSoon($days);
                    break;
                case 'no_due_date':
                    $query->whereNull('due_date');
                    break;
            }
        }

        // Filtro por período
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filtro por status (ativo/inativo)
        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            }
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Criar um novo documento
     */
    public function store(array $data): Document
    {
        DB::beginTransaction();
        
        try {
            $documentData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'comments' => $data['comments'] ?? '',
                'file_type' => $data['file_type'] ?? 'document',
                'created_by_user_id' => $data['user_id'],
                'updated_by_user_id' => $data['user_id'],
                'document_status_id' => $data['document_status_id'] ?? $this->getDefaultStatusId(),
                'due_date' => $data['due_date'] ?? null,
            ];

            $document = Document::create($documentData);

            // Upload de arquivos se fornecidos
            if (isset($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->uploadDocumentFile($document, $file);
                    }
                }
            }

            DB::commit();
            
            return $document->load(['user', 'documentStatus', 'documentFiles', 'createdByUser']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Exibir um documento específico
     */
    public function show(int $id): Document
    {
        return Document::with(['user', 'documentStatus', 'documentFiles', 'createdByUser'])
                      ->findOrFail($id);
    }

    /**
     * Atualizar um documento
     */
    public function update(int $id, array $data): Document
    {
        DB::beginTransaction();
        
        try {
            $document = Document::findOrFail($id);

            $updateData = array_filter([
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'comments' => $data['comments'] ?? null,
                'file_type' => $data['file_type'] ?? null,
                'document_status_id' => $data['document_status_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'updated_by_user_id' => $data['updated_by_user_id'] ?? auth()->id(),
            ], function ($value) {
                return $value !== null;
            });

            $document->update($updateData);

            // Upload de novos arquivos se fornecidos
            if (isset($data['files']) && is_array($data['files'])) {
                foreach ($data['files'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->uploadDocumentFile($document, $file);
                    }
                }
            }

            DB::commit();
            
            return $document->load(['user', 'documentStatus', 'documentFiles', 'createdByUser']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar um documento
     */
    public function destroy(int $id): bool
    {
        $document = Document::findOrFail($id);
        return $document->delete();
    }

    /**
     * Restaurar um documento deletado
     */
    public function restore(int $id): Document
    {
        $document = Document::withTrashed()->findOrFail($id);
        $document->restore();
        
        return $document->load(['user', 'documentStatus', 'documentFiles', 'createdByUser']);
    }

    /**
     * Deletar permanentemente um documento
     */
    public function forceDelete(int $id): bool
    {
        $document = Document::withTrashed()->findOrFail($id);
        
        // Deletar todos os arquivos associados
        foreach ($document->documentFiles as $file) {
            $file->forceDelete(); // Isso também deletará o arquivo físico
        }
        
        return $document->forceDelete();
    }

    /**
     * Upload de arquivo para um documento
     */
    public function uploadDocumentFile(Document $document, UploadedFile $file): DocumentFile
    {
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/' . $document->id, $fileName, 'public');

        return DocumentFile::create([
            'document_id' => $document->id,
            'file_path' => $path,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    /**
     * Deletar um arquivo específico de um documento
     */
    public function deleteDocumentFile(int $documentId, int $fileId): bool
    {
        $documentFile = DocumentFile::where('document_id', $documentId)
                                   ->where('id', $fileId)
                                   ->firstOrFail();
        
        return $documentFile->delete();
    }

    /**
     * Obter estatísticas dos documentos
     */
    public function getStats(array $filters = []): array
    {
        $query = Document::query();

        // Aplicar filtros se fornecidos
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $byStatus = Document::select('document_status_id', DB::raw('count(*) as total'))
                           ->with('documentStatus')
                           ->groupBy('document_status_id')
                           ->get()
                           ->mapWithKeys(function ($item) {
                               return [$item->documentStatus->name => $item->total];
                           });

        $overdue = Document::overdue()->count();
        $dueSoon = Document::dueSoon()->count();

        return [
            'total_documents' => $total,
            'documents_by_status' => $byStatus,
            'overdue_documents' => $overdue,
            'due_soon_documents' => $dueSoon,
        ];
    }

    /**
     * Obter ID do status padrão
     */
    private function getDefaultStatusId(): int
    {
        $defaultStatus = DocumentStatus::where('name', DocumentStatus::STATUS_DRAFT)->first();
        
        if (!$defaultStatus) {
            // Criar status padrão se não existir
            $defaultStatus = DocumentStatus::create(['name' => DocumentStatus::STATUS_DRAFT]);
        }
        
        return $defaultStatus->id;
    }

    /**
     * Buscar documentos
     */
    public function search(string $term, array $filters = []): Collection
    {
        $query = Document::with(['user', 'documentStatus', 'documentFiles'])
                         ->where(function ($q) use ($term) {
                             $q->where('title', 'like', "%{$term}%")
                               ->orWhere('description', 'like', "%{$term}%")
                               ->orWhere('comments', 'like', "%{$term}%");
                         });

        // Aplicar filtros adicionais
        if (isset($filters['status_id'])) {
            $query->byStatus($filters['status_id']);
        }

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        return $query->limit(20)->get();
    }

    /**
     * Obter documentos por status
     */
    public function getByStatus(int $statusId, array $filters = []): Collection
    {
        $query = Document::with(['user', 'documentFiles'])
                         ->byStatus($statusId);

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Alterar status de um documento
     */
    public function changeStatus(int $id, int $statusId, ?string $comments = null): Document
    {
        $document = Document::findOrFail($id);
        
        $updateData = [
            'document_status_id' => $statusId,
            'updated_by_user_id' => auth()->id(),
        ];

        if ($comments) {
            $updateData['comments'] = $document->comments . "\n\n" . now()->format('Y-m-d H:i:s') . " - " . $comments;
        }

        $document->update($updateData);
        
        return $document->load(['user', 'documentStatus', 'documentFiles', 'createdByUser']);
    }
}