<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Library\CreateBookRequest;
use App\Http\Requests\Api\Library\UpdateBookRequest;
use App\Services\Library\BookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function indexCategories(Request $request): JsonResponse
    {
        try {
            $books_categories = $this->bookService->indexCategories();

            return response()->json([
                'success' => true,
                'message' => 'Categorias dos Livros obtidos com sucesso.',
                'data' => $books_categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos os livros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'library_id', 'author', 'status', 'per_page']);
            $books = $this->bookService->index($filters);

            return response()->json([
                'success' => true,
                'message' => 'Livros obtidos com sucesso.',
                'data' => $books
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar um novo livro
     */
    public function store(CreateBookRequest $request): JsonResponse
    {
        try {
            $book = $this->bookService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Livro criado com sucesso.',
                'data' => $book
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar um livro específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->show($id);

            return response()->json([
                'success' => true,
                'message' => 'Livro obtido com sucesso.',
                'data' => $book
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um livro
     */
    public function update(UpdateBookRequest $request, int $id): JsonResponse
    {
        try {
            $book = $this->bookService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Livro atualizado com sucesso.',
                'data' => $book
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar (soft delete) um livro
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bookService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Livro removido com sucesso.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar um livro soft deleted
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Livro restaurado com sucesso.',
                'data' => $book
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado ou não está excluído.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar permanentemente um livro
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->bookService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Livro deletado permanentemente.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar livros
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only(['title', 'author', 'editor', 'topic', 'library_id']);
            $books = $this->bookService->search($criteria);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $books
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter livros por biblioteca
     */
    public function getByLibrary(int $libraryId): JsonResponse
    {
        try {
            $books = $this->bookService->getByLibrary($libraryId);

            return response()->json([
                'success' => true,
                'message' => 'Livros da biblioteca obtidos com sucesso.',
                'data' => $books
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas de livros
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->bookService->getStats();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas obtidas com sucesso.',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar um livro
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->duplicate($id);

            return response()->json([
                'success' => true,
                'message' => 'Livro duplicado com sucesso.',
                'data' => $book
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download do arquivo do livro
     */
    public function downloadFile(int $id)
    {
        try {
            $book = $this->bookService->show($id);

            if (!$book->book_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado para este livro.'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $book->book_file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo físico não encontrado.'
                ], 404);
            }

            // Usar título do livro como nome do arquivo
            $filename = $book->title . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            
            return response()->download($filePath, $filename);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livro não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
