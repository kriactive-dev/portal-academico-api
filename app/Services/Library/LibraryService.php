<?php

namespace App\Services\Library;

use App\Models\Library\Library;
use App\Models\Library\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LibraryService
{
    /**
     * Listar todas as bibliotecas com paginação
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = Library::with(['creator', 'books'])
            ->withTrashed()
            ->withCount('books');

        // Aplicar filtros se fornecidos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            }
        }

        if (isset($filters['has_books'])) {
            if ($filters['has_books'] === 'true') {
                $query->has('books');
            } elseif ($filters['has_books'] === 'false') {
                $query->doesntHave('books');
            }
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Criar uma nova biblioteca
     */
    public function store(array $data): Library
    {
        DB::beginTransaction();
        
        try {
            $libraryData = [
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
            ];

            $library = Library::create($libraryData);

            DB::commit();
            
            return $library->load(['creator', 'books']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mostrar uma biblioteca específica
     */
    public function show(int $id): Library
    {
        $library = Library::with(['creator', 'books.creator'])
            ->withTrashed()
            ->withCount('books')
            ->find($id);

        if (!$library) {
            throw ValidationException::withMessages([
                'library' => ['Biblioteca não encontrada.']
            ]);
        }

        return $library;
    }

    /**
     * Atualizar uma biblioteca
     */
    public function update(int $id, array $data): Library
    {
        DB::beginTransaction();
        
        try {
            $library = Library::withTrashed()->find($id);
            
            if (!$library) {
                throw ValidationException::withMessages([
                    'library' => ['Biblioteca não encontrada.']
                ]);
            }

            // Atualizar dados básicos da biblioteca
            $libraryData = array_filter([
                'name' => $data['name'] ?? null,
                'address' => $data['address'] ?? null,
            ], function ($value) {
                return $value !== null;
            });

            if (!empty($libraryData)) {
                $library->update($libraryData);
            }

            DB::commit();
            
            return $library->fresh(['creator', 'books']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar (soft delete) uma biblioteca
     */
    public function destroy(int $id): bool
    {
        $library = Library::find($id);
        
        if (!$library) {
            throw ValidationException::withMessages([
                'library' => ['Biblioteca não encontrada.']
            ]);
        }

        // Verificar se tem livros associados
        if ($library->books()->count() > 0) {
            throw ValidationException::withMessages([
                'library' => ['Não é possível excluir uma biblioteca que possui livros. Remova os livros primeiro.']
            ]);
        }

        return $library->delete();
    }

    /**
     * Restaurar uma biblioteca soft deleted
     */
    public function restore(int $id): Library
    {
        $library = Library::onlyTrashed()->find($id);
        
        if (!$library) {
            throw ValidationException::withMessages([
                'library' => ['Biblioteca não encontrada ou não está excluída.']
            ]);
        }

        $library->restore();
        
        return $library->load(['creator', 'books']);
    }

    /**
     * Deletar permanentemente uma biblioteca
     */
    public function forceDelete(int $id): bool
    {
        $library = Library::withTrashed()->find($id);
        
        if (!$library) {
            throw ValidationException::withMessages([
                'library' => ['Biblioteca não encontrada.']
            ]);
        }

        // Verificar se tem livros associados
        if ($library->books()->count() > 0) {
            throw ValidationException::withMessages([
                'library' => ['Não é possível excluir permanentemente uma biblioteca que possui livros.']
            ]);
        }

        return $library->forceDelete();
    }

    /**
     * Ativar/Desativar biblioteca
     */
    public function toggleStatus(int $id): Library
    {
        $library = Library::withTrashed()->find($id);
        
        if (!$library) {
            throw ValidationException::withMessages([
                'library' => ['Biblioteca não encontrada.']
            ]);
        }

        if ($library->trashed()) {
            $library->restore();
        } else {
            // Verificar se tem livros antes de desativar
            if ($library->books()->count() > 0) {
                throw ValidationException::withMessages([
                    'library' => ['Não é possível desativar uma biblioteca que possui livros ativos.']
                ]);
            }
            $library->delete();
        }

        return $library->fresh(['creator', 'books']);
    }

    /**
     * Buscar bibliotecas por critérios específicos
     */
    public function search(array $criteria): Collection
    {
        $query = Library::with(['creator'])
            ->withCount('books');

        foreach ($criteria as $field => $value) {
            if (in_array($field, ['name', 'address'])) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query->get();
    }

    /**
     * Obter livros de uma biblioteca específica
     */
    public function getBooks(int $libraryId, array $filters = []): LengthAwarePaginator
    {
        $library = $this->show($libraryId);
        
        $query = $library->books()
            ->with(['creator'])
            ->withTrashed();

        // Aplicar filtros
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

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
     * Transferir livros entre bibliotecas
     */
    public function transferBooks(int $fromLibraryId, int $toLibraryId, array $bookIds): array
    {
        DB::beginTransaction();
        
        try {
            $fromLibrary = $this->show($fromLibraryId);
            $toLibrary = $this->show($toLibraryId);
            
            // Verificar se os livros pertencem à biblioteca de origem
            $books = Book::whereIn('id', $bookIds)
                ->where('library_id', $fromLibraryId)
                ->get();
            
            if ($books->count() !== count($bookIds)) {
                throw ValidationException::withMessages([
                    'books' => ['Alguns livros não foram encontrados na biblioteca de origem.']
                ]);
            }

            // Transferir os livros
            Book::whereIn('id', $bookIds)->update([
                'library_id' => $toLibraryId
            ]);

            DB::commit();
            
            return [
                'transferred_count' => $books->count(),
                'from_library' => $fromLibrary->name,
                'to_library' => $toLibrary->name,
                'books' => $books->pluck('title')->toArray()
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obter estatísticas de bibliotecas
     */
    public function getStats(): array
    {
        return [
            'total' => Library::withTrashed()->count(),
            'active' => Library::count(),
            'inactive' => Library::onlyTrashed()->count(),
            'with_books' => Library::has('books')->count(),
            'without_books' => Library::doesntHave('books')->count(),
            'total_books' => Book::count(),
            'books_by_library' => Library::withCount('books')
                ->get()
                ->mapWithKeys(function ($library) {
                    return [$library->name => $library->books_count];
                }),
            'recent' => Library::where('created_at', '>=', now()->subDays(30))->count(),
            'top_libraries' => Library::withCount('books')
                ->orderBy('books_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'books_count'])
        ];
    }

    /**
     * Duplicar uma biblioteca (sem os livros)
     */
    public function duplicate(int $id): Library
    {
        $originalLibrary = $this->show($id);
        
        $duplicateData = $originalLibrary->toArray();
        unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at'], $duplicateData['deleted_at']);
        
        // Adicionar sufixo ao nome
        $duplicateData['name'] = $duplicateData['name'] . ' (Cópia)';
        
        return Library::create($duplicateData);
    }

    /**
     * Obter bibliotecas com seus livros mais populares
     */
    public function getLibrariesWithPopularBooks(): Collection
    {
        return Library::with(['books' => function ($query) {
            $query->select(['id', 'title', 'author', 'library_id', 'created_at'])
                  ->orderBy('created_at', 'desc')
                  ->limit(5);
        }])
        ->withCount('books')
        ->orderBy('books_count', 'desc')
        ->get();
    }

    /**
     * Validar se biblioteca pode ser excluída
     */
    public function canBeDeleted(int $id): array
    {
        $library = $this->show($id);
        
        $booksCount = $library->books()->count();
        $activeBooksCount = $library->books()->whereNull('deleted_at')->count();
        
        return [
            'can_delete' => $booksCount === 0,
            'reason' => $booksCount > 0 ? 'Biblioteca possui livros associados' : null,
            'books_count' => $booksCount,
            'active_books_count' => $activeBooksCount,
            'suggestions' => $booksCount > 0 ? [
                'Remova todos os livros primeiro',
                'Ou transfira os livros para outra biblioteca'
            ] : []
        ];
    }

    /**
     * Obter estatísticas específicas de uma biblioteca
     */
    public function getLibraryStats(int $id): array
    {
        $library = $this->show($id);
        
        return [
            'library' => [
                'id' => $library->id,
                'name' => $library->name,
                'address' => $library->address,
                'created_at' => $library->created_at
            ],
            'books' => [
                'total' => $library->books()->count(),
                'active' => $library->books()->whereNull('deleted_at')->count(),
                'inactive' => $library->books()->onlyTrashed()->count(),
                'with_files' => $library->books()->whereNotNull('book_file_path')->count(),
                'without_files' => $library->books()->whereNull('book_file_path')->count()
            ],
            'popular_books' => $library->books()
                ->select(['id', 'title', 'author', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_books' => $library->books()
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'books_by_topic' => $library->books()
                ->select('topic')
                ->whereNotNull('topic')
                ->groupBy('topic')
                ->selectRaw('topic, count(*) as count')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->topic => $item->count];
                })
        ];
    }

    /**
     * Obter contagem detalhada de livros de uma biblioteca
     */
    public function getBooksCount(int $id): array
    {
        $library = $this->show($id);
        
        return [
            'library_id' => $library->id,
            'library_name' => $library->name,
            'total_books' => $library->books()->count(),
            'active_books' => $library->books()->whereNull('deleted_at')->count(),
            'inactive_books' => $library->books()->onlyTrashed()->count(),
            'books_with_files' => $library->books()->whereNotNull('book_file_path')->count(),
            'books_without_files' => $library->books()->whereNull('book_file_path')->count(),
            'recent_books' => $library->books()->where('created_at', '>=', now()->subDays(30))->count()
        ];
    }
}
