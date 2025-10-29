<?php

namespace App\Http\Controllers\Api\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\DocumentTypeRequest;
use App\Services\Documents\DocumentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    protected DocumentTypeService $documentTypeService;

    public function __construct(DocumentTypeService $documentTypeService)
    {
        $this->documentTypeService = $documentTypeService;
    }

    /**
     * Listar todos os tipos de documento com paginação
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            if ($search) {
                $documentTypes = $this->documentTypeService->search($search, $perPage);
            } else {
                $documentTypes = $this->documentTypeService->getAllPaginated($perPage);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipos de documento listados com sucesso.',
                'data' => $documentTypes->items(),
                'meta' => [
                    'current_page' => $documentTypes->currentPage(),
                    'last_page' => $documentTypes->lastPage(),
                    'per_page' => $documentTypes->perPage(),
                    'total' => $documentTypes->total(),
                    'from' => $documentTypes->firstItem(),
                    'to' => $documentTypes->lastItem(),
                ],
                'links' => [
                    'first' => $documentTypes->url(1),
                    'last' => $documentTypes->url($documentTypes->lastPage()),
                    'prev' => $documentTypes->previousPageUrl(),
                    'next' => $documentTypes->nextPageUrl(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos os tipos de documento sem paginação
     *
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        try {
            $documentTypes = $this->documentTypeService->getAll();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de documento listados com sucesso.',
                'data' => $documentTypes,
                'total' => $documentTypes->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir um tipo de documento específico
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $documentType = $this->documentTypeService->getById($id);

            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento encontrado com sucesso.',
                'data' => $documentType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar um novo tipo de documento
     *
     * @param DocumentTypeRequest $request
     * @return JsonResponse
     */
    public function store(DocumentTypeRequest $request): JsonResponse
    {
        try {
            // Verificar se o nome já existe
            if ($this->documentTypeService->nameExists($request->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um tipo de documento com este nome.',
                    'errors' => [
                        'name' => ['Já existe um tipo de documento com este nome.']
                    ]
                ], 422);
            }

            $documentType = $this->documentTypeService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento criado com sucesso.',
                'data' => $documentType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um tipo de documento
     *
     * @param DocumentTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(DocumentTypeRequest $request, int $id): JsonResponse
    {
        try {
            // Verificar se o nome já existe (excluindo o próprio registro)
            if ($this->documentTypeService->nameExists($request->name, $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um tipo de documento com este nome.',
                    'errors' => [
                        'name' => ['Já existe um tipo de documento com este nome.']
                    ]
                ], 422);
            }

            $documentType = $this->documentTypeService->update($id, $request->validated());

            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento atualizado com sucesso.',
                'data' => $documentType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar um tipo de documento (soft delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->documentTypeService->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento deletado com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar um tipo de documento deletado
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->documentTypeService->restore($id);

            if (!$restored) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento não encontrado ou não está deletado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento restaurado com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar permanentemente um tipo de documento
     *
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->documentTypeService->forceDelete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento deletado permanentemente com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar permanentemente tipo de documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar tipos de documento deletados
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trashed(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $documentTypes = $this->documentTypeService->getTrashed($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Tipos de documento deletados listados com sucesso.',
                'data' => $documentTypes->items(),
                'meta' => [
                    'current_page' => $documentTypes->currentPage(),
                    'last_page' => $documentTypes->lastPage(),
                    'per_page' => $documentTypes->perPage(),
                    'total' => $documentTypes->total(),
                    'from' => $documentTypes->firstItem(),
                    'to' => $documentTypes->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tipos de documento deletados.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas dos tipos de documento
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Estatísticas dos tipos de documento.',
                'data' => [
                    'total' => $this->documentTypeService->count(),
                    'trashed' => $this->documentTypeService->countTrashed(),
                    'active' => $this->documentTypeService->count() - $this->documentTypeService->countTrashed()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}