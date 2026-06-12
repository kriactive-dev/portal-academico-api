<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Library\CreateLibraryRequest;
use App\Http\Requests\Api\Library\UpdateLibraryRequest;
use App\Http\Requests\Api\Library\TransferBooksRequest;
use App\Services\Library\LibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LibraryController extends Controller
{
    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * Listar todas as bibliotecas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'has_books', 'per_page']);
            $libraries = $this->libraryService->index($filters);

            return response()->json([
                'success' => true,
                'message' => 'Bibliotecas obtidas com sucesso.',
                'data' => $libraries
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
     * Criar uma nova biblioteca
     */
    public function store(CreateLibraryRequest $request): JsonResponse
    {
        try {
            $library = $this->libraryService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca criada com sucesso.',
                'data' => $library
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
     * Mostrar uma biblioteca específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $library = $this->libraryService->show($id);

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca obtida com sucesso.',
                'data' => $library
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Atualizar uma biblioteca
     */
    public function update(UpdateLibraryRequest $request, int $id): JsonResponse
    {
        try {
            $library = $this->libraryService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca atualizada com sucesso.',
                'data' => $library
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Deletar (soft delete) uma biblioteca
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->libraryService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca removida com sucesso.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover biblioteca.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar uma biblioteca soft deleted
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $library = $this->libraryService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca restaurada com sucesso.',
                'data' => $library
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada ou não está excluída.',
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
     * Deletar permanentemente uma biblioteca
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->libraryService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca deletada permanentemente.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar biblioteca.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/Desativar biblioteca
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $library = $this->libraryService->toggleStatus($id);

            $message = $library->trashed() ? 'Biblioteca desativada' : 'Biblioteca ativada';

            return response()->json([
                'success' => true,
                'message' => $message . ' com sucesso.',
                'data' => $library
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status da biblioteca.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar bibliotecas
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only(['name', 'address']);
            $libraries = $this->libraryService->search($criteria);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $libraries
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
     * Obter livros de uma biblioteca
     */
    public function getBooks(int $id, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'per_page']);
            $books = $this->libraryService->getBooks($id, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Livros da biblioteca obtidos com sucesso.',
                'data' => $books
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Transferir livros entre bibliotecas
     */
    public function transferBooks(TransferBooksRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->libraryService->transferBooks(
                $data['from_library_id'],
                $data['to_library_id'],
                $data['book_ids']
            );

            return response()->json([
                'success' => true,
                'message' => 'Livros transferidos com sucesso.',
                'data' => $result
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na transferência de livros.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas de bibliotecas
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->libraryService->getStats();

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
     * Duplicar uma biblioteca
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $library = $this->libraryService->duplicate($id);

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca duplicada com sucesso.',
                'data' => $library
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Obter bibliotecas com livros populares
     */
    public function getWithPopularBooks(): JsonResponse
    {
        try {
            $libraries = $this->libraryService->getLibrariesWithPopularBooks();

            return response()->json([
                'success' => true,
                'message' => 'Bibliotecas com livros populares obtidas com sucesso.',
                'data' => $libraries
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
     * Verificar se biblioteca pode ser excluída
     */
    public function canBeDeleted(int $id): JsonResponse
    {
        try {
            $result = $this->libraryService->canBeDeleted($id);

            return response()->json([
                'success' => true,
                'message' => 'Verificação realizada com sucesso.',
                'data' => $result
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Obter estatísticas específicas de uma biblioteca
     */
    public function getLibraryStats(int $id): JsonResponse
    {
        try {
            $stats = $this->libraryService->getLibraryStats($id);

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas da biblioteca obtidas com sucesso.',
                'data' => $stats
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
     * Obter contagem de livros de uma biblioteca
     */
    public function getBooksCount(int $id): JsonResponse
    {
        try {
            $count = $this->libraryService->getBooksCount($id);

            return response()->json([
                'success' => true,
                'message' => 'Contagem de livros obtida com sucesso.',
                'data' => $count
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Biblioteca não encontrada.',
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
