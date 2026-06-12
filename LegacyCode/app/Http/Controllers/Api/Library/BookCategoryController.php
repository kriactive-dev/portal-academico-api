<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\BookCategoryRequest;
use App\Services\Library\BookCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class BookCategoryController extends Controller
{
    protected BookCategoryService $bookCategoryService;

    public function __construct(BookCategoryService $bookCategoryService)
    {
        $this->bookCategoryService = $bookCategoryService;
    }

    /**
     * Lista todas as categorias de livros com paginação
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $search = $request->get('search');

            $categories = $this->bookCategoryService->getAllPaginated($perPage, $search);

            return response()->json([
                'success' => true,
                'message' => 'Categorias de livros listadas com sucesso.',
                'data' => $categories->items(),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
                'links' => [
                    'first' => $categories->url(1),
                    'last' => $categories->url($categories->lastPage()),
                    'prev' => $categories->previousPageUrl(),
                    'next' => $categories->nextPageUrl(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias de livros.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista todas as categorias sem paginação
     */
    public function all(): JsonResponse
    {
        try {
            $categories = $this->bookCategoryService->getAll();

            return response()->json([
                'success' => true,
                'message' => 'Categorias de livros listadas com sucesso.',
                'data' => $categories,
                'total' => $categories->count(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias de livros.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista categorias com contagem de livros
     */
    public function withBooksCount(): JsonResponse
    {
        try {
            $categories = $this->bookCategoryService->getWithBooksCount();

            return response()->json([
                'success' => true,
                'message' => 'Categorias com contagem de livros listadas com sucesso.',
                'data' => $categories,
                'total' => $categories->count(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias com contagem de livros.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibe uma categoria específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->bookCategoryService->getById($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro encontrada com sucesso.',
                'data' => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cria uma nova categoria de livro
     */
    public function store(BookCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->bookCategoryService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro criada com sucesso.',
                'data' => $category,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Atualiza uma categoria existente
     */
    public function update(BookCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->bookCategoryService->getById($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada.',
                ], 404);
            }

            $updatedCategory = $this->bookCategoryService->update($category, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro atualizada com sucesso.',
                'data' => $updatedCategory,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove uma categoria (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $category = $this->bookCategoryService->getById($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada.',
                ], 404);
            }

            $this->bookCategoryService->delete($category);

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro deletada com sucesso.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restaura uma categoria deletada
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->bookCategoryService->restore($id);

            if (!$restored) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada na lixeira.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro restaurada com sucesso.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove permanentemente uma categoria
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->bookCategoryService->forceDelete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro deletada permanentemente com sucesso.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar permanentemente categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista categorias deletadas
     */
    public function trashed(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $categories = $this->bookCategoryService->getTrashed($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Categorias de livros deletadas listadas com sucesso.',
                'data' => $categories->items(),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias deletadas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplica uma categoria
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $category = $this->bookCategoryService->getById($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoria de livro não encontrada.',
                ], 404);
            }

            $duplicatedCategory = $this->bookCategoryService->duplicate($category);

            return response()->json([
                'success' => true,
                'message' => 'Categoria de livro duplicada com sucesso.',
                'data' => $duplicatedCategory,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar categoria de livro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna categorias mais usadas
     */
    public function mostUsed(): JsonResponse
    {
        try {
            $categories = $this->bookCategoryService->getMostUsed();

            return response()->json([
                'success' => true,
                'message' => 'Categorias mais usadas listadas com sucesso.',
                'data' => $categories,
                'total' => $categories->count(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias mais usadas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna estatísticas das categorias
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->bookCategoryService->getStats();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas das categorias de livros.',
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas das categorias.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
