<?php

namespace App\Services\Library;

use App\Models\Library\BookCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class BookCategoryService
{
    /**
     * Lista todas as categorias com paginação
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = BookCategory::query()
            ->alphabetical()
            ->search($search);

        return $query->paginate($perPage);
    }

    /**
     * Lista todas as categorias sem paginação
     */
    public function getAll(): Collection
    {
        return BookCategory::alphabetical()->get();
    }

    /**
     * Busca categorias pelo nome
     */
    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return BookCategory::search($search)
            ->alphabetical()
            ->paginate($perPage);
    }

    /**
     * Encontra uma categoria pelo ID
     */
    public function getById(int $id): ?BookCategory
    {
        return BookCategory::find($id);
    }

    /**
     * Cria uma nova categoria
     */
    public function create(array $data): BookCategory
    {
        DB::beginTransaction();
        
        try {
            $category = BookCategory::create([
                'name' => $data['name'],
                'created_by_user_id' => auth()->id(),
            ]);

            DB::commit();
            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualiza uma categoria existente
     */
    public function update(BookCategory $category, array $data): BookCategory
    {
        DB::beginTransaction();
        
        try {
            $category->update([
                'name' => $data['name'],
                'updated_by_user_id' => auth()->id(),
            ]);

            DB::commit();
            return $category->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove uma categoria (soft delete)
     */
    public function delete(BookCategory $category): bool
    {
        DB::beginTransaction();
        
        try {
            $category->update(['deleted_by_user_id' => auth()->id()]);
            $result = $category->delete();

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restaura uma categoria deletada
     */
    public function restore(int $id): bool
    {
        $category = BookCategory::onlyTrashed()->find($id);
        
        if (!$category) {
            return false;
        }

        DB::beginTransaction();
        
        try {
            $category->update(['deleted_by_user_id' => null]);
            $result = $category->restore();

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove permanentemente uma categoria
     */
    public function forceDelete(int $id): bool
    {
        $category = BookCategory::withTrashed()->find($id);
        
        if (!$category) {
            return false;
        }

        DB::beginTransaction();
        
        try {
            $result = $category->forceDelete();

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lista categorias deletadas
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return BookCategory::onlyTrashed()
            ->alphabetical()
            ->paginate($perPage);
    }

    /**
     * Verifica se uma categoria com o nome já existe
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = BookCategory::where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Conta o total de categorias ativas
     */
    public function count(): int
    {
        return BookCategory::count();
    }

    /**
     * Conta o total de categorias deletadas
     */
    public function countTrashed(): int
    {
        return BookCategory::onlyTrashed()->count();
    }

    /**
     * Obter categoria com contagem de livros
     */
    public function getWithBooksCount(): Collection
    {
        return BookCategory::withCount('books')
            ->alphabetical()
            ->get();
    }

    /**
     * Duplicar uma categoria
     */
    public function duplicate(BookCategory $category): BookCategory
    {
        DB::beginTransaction();
        
        try {
            $newCategory = BookCategory::create([
                'name' => $category->name . ' (Cópia)',
                'created_by_user_id' => auth()->id(),
            ]);

            DB::commit();
            return $newCategory;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obter categorias mais usadas
     */
    public function getMostUsed(int $limit = 10): Collection
    {
        return BookCategory::withCount('books')
            ->orderBy('books_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obter estatísticas das categorias
     */
    public function getStats(): array
    {
        return [
            'total' => $this->count(),
            'trashed' => $this->countTrashed(),
            'active' => $this->count(),
            'with_books' => BookCategory::has('books')->count(),
            'without_books' => BookCategory::doesntHave('books')->count(),
        ];
    }
}
