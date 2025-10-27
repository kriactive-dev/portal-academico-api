<?php

namespace App\Services\Library;

use App\Models\Library\Book;
use App\Models\Library\BookCategory;
use App\Models\Library\Library;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class BookService
{

    public function indexCategories(): LengthAwarePaginator
    {
        $query = BookCategory::query()->withTrashed();

    
        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    /**
     * Listar todos os livros com paginação
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = Book::with(['library', 'creator', 'updater'])
            ->withTrashed();

        // Aplicar filtros se fornecidos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('editor', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        if (isset($filters['library_id'])) {
            $query->byLibrary($filters['library_id']);
        }

        if (isset($filters['author'])) {
            $query->byAuthor($filters['author']);
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
     * Criar um novo livro
     */
    public function store(array $data): Book
    {
        DB::beginTransaction();
        
        try {
            // Processar uploads de arquivos
            $bookData = [
                'title' => $data['title'],
                'author' => $data['author'] ?? null,
                'editor' => $data['editor'] ?? null,
                'cdu' => $data['cdu'] ?? null,
                'topic' => $data['topic'] ?? null,
                'edition' => $data['edition'] ?? null,
                'launch_date' => $data['launch_date'] ?? null,
                'launch_place' => $data['launch_place'] ?? null,
                'library_id' => $data['library_id'] ?? null,
                'book_category_id' => $data['book_category_id'] ?? null,
                'book_file_path' => '',
                'book_img_path' => '',
                'book_cover_path' => '',
            ];

            // Upload do arquivo do livro
            if (isset($data['book_file']) && $data['book_file'] instanceof UploadedFile) {
                $bookPath = $data['book_file']->store('books', 'public');
                $bookData['book_file_path'] = $bookPath;
            }

            // Upload da capa do livro
            if (isset($data['book_cover']) && $data['book_cover'] instanceof UploadedFile) {
                $coverPath = $data['book_cover']->store('covers', 'public');
                $bookData['book_cover_path'] = $coverPath;
            }

            // Upload da imagem adicional (se fornecida)
            if (isset($data['book_img']) && $data['book_img'] instanceof UploadedFile) {
                $imgPath = $data['book_img']->store('images', 'public');
                $bookData['book_img_path'] = $imgPath;
            }

            $book = Book::create($bookData);

            DB::commit();
            
            return $book->load(['library', 'creator']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpar arquivos se houve erro
            if (isset($bookPath)) {
                Storage::disk('public')->delete($bookPath);
            }
            if (isset($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            
            throw $e;
        }
    }

    /**
     * Mostrar um livro específico
     */
    public function show(int $id): Book
    {
        $book = Book::with(['library', 'creator', 'updater'])
            ->withTrashed()
            ->find($id);

        if (!$book) {
            throw ValidationException::withMessages([
                'book' => ['Livro não encontrado.']
            ]);
        }

        return $book;
    }

    /**
     * Atualizar um livro
     */
    public function update(int $id, array $data): Book
    {
        DB::beginTransaction();
        
        try {
            $book = Book::withTrashed()->find($id);
            
            if (!$book) {
                throw ValidationException::withMessages([
                    'book' => ['Livro não encontrado.']
                ]);
            }

            // Dados básicos do livro
            $bookData = array_filter([
                'title' => $data['title'] ?? null,
                'author' => $data['author'] ?? null,
                'editor' => $data['editor'] ?? null,
                'cdu' => $data['cdu'] ?? null,
                'topic' => $data['topic'] ?? null,
                'edition' => $data['edition'] ?? null,
                'launch_date' => $data['launch_date'] ?? null,
                'launch_place' => $data['launch_place'] ?? null,
                'library_id' => $data['library_id'] ?? null,
            ], function ($value) {
                return $value !== null;
            });

            // Upload do arquivo do livro
            if (isset($data['book_file']) && $data['book_file'] instanceof UploadedFile) {
                // Deletar arquivo anterior se existir
                if ($book->book_file_path) {
                    Storage::disk('public')->delete($book->book_file_path);
                }
                
                $bookPath = $data['book_file']->store('books', 'public');
                $bookData['book_file_path'] = $bookPath;
            }

            // Upload da capa do livro
            if (isset($data['cover_file']) && $data['cover_file'] instanceof UploadedFile) {
                // Deletar capa anterior se existir
                if ($book->book_cover_path) {
                    Storage::disk('public')->delete($book->book_cover_path);
                }
                
                $coverPath = $data['cover_file']->store('covers', 'public');
                $bookData['book_cover_path'] = $coverPath;
            }

            // Upload da imagem adicional
            if (isset($data['img_file']) && $data['img_file'] instanceof UploadedFile) {
                // Deletar imagem anterior se existir
                if ($book->book_img_path) {
                    Storage::disk('public')->delete($book->book_img_path);
                }
                
                $imgPath = $data['img_file']->store('images', 'public');
                $bookData['book_img_path'] = $imgPath;
            }

            if (!empty($bookData)) {
                $book->update($bookData);
            }

            DB::commit();
            
            return $book->fresh(['library', 'creator', 'updater']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpar novos arquivos se houve erro
            if (isset($bookPath)) {
                Storage::disk('public')->delete($bookPath);
            }
            if (isset($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            
            throw $e;
        }
    }

    /**
     * Deletar (soft delete) um livro
     */
    public function destroy(int $id): bool
    {
        $book = Book::find($id);
        
        if (!$book) {
            throw ValidationException::withMessages([
                'book' => ['Livro não encontrado.']
            ]);
        }

        return $book->delete();
    }

    /**
     * Restaurar um livro soft deleted
     */
    public function restore(int $id): Book
    {
        $book = Book::onlyTrashed()->find($id);
        
        if (!$book) {
            throw ValidationException::withMessages([
                'book' => ['Livro não encontrado ou não está excluído.']
            ]);
        }

        $book->restore();
        
        return $book->load(['library', 'creator']);
    }

    /**
     * Deletar permanentemente um livro
     */
    public function forceDelete(int $id): bool
    {
        $book = Book::withTrashed()->find($id);
        
        if (!$book) {
            throw ValidationException::withMessages([
                'book' => ['Livro não encontrado.']
            ]);
        }

        // Deletar arquivos físicos
        if ($book->book_file_path) {
            Storage::disk('public')->delete($book->book_file_path);
        }
        if ($book->book_cover_path) {
            Storage::disk('public')->delete($book->book_cover_path);
        }
        if ($book->book_img_path) {
            Storage::disk('public')->delete($book->book_img_path);
        }

        return $book->forceDelete();
    }

    /**
     * Buscar livros por critérios específicos
     */
    public function search(array $criteria): Collection
    {
        $query = Book::with(['library', 'creator']);

        foreach ($criteria as $field => $value) {
            if (in_array($field, ['title', 'author', 'editor', 'topic'])) {
                $query->where($field, 'like', "%{$value}%");
            } elseif ($field === 'library_id') {
                $query->where('library_id', $value);
            }
        }

        return $query->get();
    }

    /**
     * Obter livros por biblioteca
     */
    public function getByLibrary(int $libraryId): Collection
    {
        return Book::with(['creator'])
            ->byLibrary($libraryId)
            ->get();
    }

    /**
     * Obter estatísticas de livros
     */
    public function getStats(): array
    {
        return [
            'total' => Book::withTrashed()->count(),
            'active' => Book::count(),
            'inactive' => Book::onlyTrashed()->count(),
            'by_library' => Book::select('library_id', DB::raw('count(*) as total'))
                ->whereNotNull('library_id')
                ->groupBy('library_id')
                ->with('library:id,name')
                ->get()
                ->pluck('total', 'library.name'),
            'recent' => Book::where('created_at', '>=', now()->subDays(30))->count(),
            'with_files' => Book::whereNotNull('book_file_path')
                ->where('book_file_path', '!=', '')
                ->count(),
            'with_covers' => Book::whereNotNull('book_cover_path')
                ->where('book_cover_path', '!=', '')
                ->count(),
            'with_images' => Book::whereNotNull('book_img_path')
                ->where('book_img_path', '!=', '')
                ->count(),
        ];
    }

    /**
     * Duplicar um livro
     */
    public function duplicate(int $id): Book
    {
        $originalBook = $this->show($id);
        
        $duplicateData = $originalBook->toArray();
        unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at'], $duplicateData['deleted_at'],$duplicateData['library'],$duplicateData['creator'],$duplicateData['updater']);
        
        // Adicionar sufixo ao título
        $duplicateData['title'] = $duplicateData['title'] . ' (Cópia)';
        
        return Book::create($duplicateData);
    }
}
