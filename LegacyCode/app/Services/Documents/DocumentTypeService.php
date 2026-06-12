<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentTypeService
{
    /**
     * Obter todos os tipos de documento com paginação
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return DocumentType::orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Obter todos os tipos de documento sem paginação
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return DocumentType::orderBy('name')->get();
    }

    /**
     * Buscar tipos de documento por nome
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return DocumentType::where('name', 'LIKE', "%{$search}%")
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Obter um tipo de documento por ID
     *
     * @param int $id
     * @return DocumentType|null
     */
    public function getById(int $id): ?DocumentType
    {
        return DocumentType::find($id);
    }

    /**
     * Criar um novo tipo de documento
     *
     * @param array $data
     * @return DocumentType
     */
    public function create(array $data): DocumentType
    {
        return DocumentType::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Atualizar um tipo de documento
     *
     * @param int $id
     * @param array $data
     * @return DocumentType|null
     */
    public function update(int $id, array $data): ?DocumentType
    {
        $documentType = $this->getById($id);
        
        if (!$documentType) {
            return null;
        }

        $documentType->update([
            'name' => $data['name'],
        ]);

        return $documentType;
    }

    /**
     * Deletar um tipo de documento (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $documentType = $this->getById($id);
        
        if (!$documentType) {
            return false;
        }

        return $documentType->delete();
    }

    /**
     * Restaurar um tipo de documento deletado
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        $documentType = DocumentType::withTrashed()->find($id);
        
        if (!$documentType) {
            return false;
        }

        return $documentType->restore();
    }

    /**
     * Deletar permanentemente um tipo de documento
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
        $documentType = DocumentType::withTrashed()->find($id);
        
        if (!$documentType) {
            return false;
        }

        return $documentType->forceDelete();
    }

    /**
     * Obter tipos de documento deletados
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return DocumentType::onlyTrashed()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Verificar se o nome do tipo de documento já existe
     *
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = DocumentType::where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Contar total de tipos de documento
     *
     * @return int
     */
    public function count(): int
    {
        return DocumentType::count();
    }

    /**
     * Contar tipos de documento deletados
     *
     * @return int
     */
    public function countTrashed(): int
    {
        return DocumentType::onlyTrashed()->count();
    }
}