<?php

namespace App\Http\Controllers\Api\Publication;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Models\Publication;
use App\Services\Publication\PublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class PublicationController extends Controller
{
    protected PublicationService $publicationService;

    public function __construct(PublicationService $publicationService)
    {
        $this->publicationService = $publicationService;
    }

    /**
     * @OA\Get(
     *     path="/api/publications",
     *     summary="Listar publicações",
     *     tags={"Publications"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active", "expired", "expiring_soon"})),
     *     @OA\Response(response=200, description="Lista de publicações")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'title', 'status', 'has_file', 
                'created_from', 'created_to', 'expires_from', 'expires_to', 
                'per_page'
            ]);

            $publications = $this->publicationService->listPublications($filters);

            return response()->json([
                'success' => true,
                'message' => 'Publicações recuperadas com sucesso.',
                'data' => $publications->items(),
                'pagination' => [
                    'current_page' => $publications->currentPage(),
                    'last_page' => $publications->lastPage(),
                    'per_page' => $publications->perPage(),
                    'total' => $publications->total(),
                    'from' => $publications->firstItem(),
                    'to' => $publications->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar publicações.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function indexUniversity(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'title', 'status', 'has_file', 
                'created_from', 'created_to', 'expires_from', 'expires_to', 
                'per_page'
            ]);

            $publications = $this->publicationService->listUniversities($filters);

            return response()->json([
                'success' => true,
                'message' => 'Universidades recuperadas com sucesso.',
                'data' => $publications->items(),
                'pagination' => [
                    'current_page' => $publications->currentPage(),
                    'last_page' => $publications->lastPage(),
                    'per_page' => $publications->perPage(),
                    'total' => $publications->total(),
                    'from' => $publications->firstItem(),
                    'to' => $publications->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar publicações.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/publications",
     *     summary="Criar publicação",
     *     tags={"Publications"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StorePublicationRequest")),
     *     @OA\Response(response=201, description="Publicação criada com sucesso")
     * )
     */
    public function store(StorePublicationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $file = $request->file('file');

            $publication = $this->publicationService->createPublication($data, $file);

            return response()->json([
                'success' => true,
                'message' => 'Publicação criada com sucesso.',
                'data' => $publication
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/publications/{id}",
     *     summary="Visualizar publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalhes da publicação")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            // Adicionar informações extras
            $publicationData = $publication->toArray();
            $publicationData['file_info'] = null;
            
            if ($publication->hasFile()) {
                $publicationData['file_info'] = [
                    'name' => $publication->getFileName(),
                    'size' => $publication->getFileSize(),
                    'size_formatted' => $publication->getFileSizeFormatted(),
                    'url' => $publication->getFileUrl(),
                ];
            }

            $publicationData['status_info'] = [
                'status' => $publication->getExpirationStatus(),
                'is_expired' => $publication->isExpired(),
                'is_active' => $publication->isActive(),
                'days_until_expiration' => $publication->daysUntilExpiration(),
            ];

            $publicationData['content_info'] = [
                'excerpt' => $publication->excerpt,
                'word_count' => $publication->word_count,
                'reading_time' => $publication->reading_time,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Publicação recuperada com sucesso.',
                'data' => $publicationData
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/publications/{id}",
     *     summary="Atualizar publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdatePublicationRequest")),
     *     @OA\Response(response=200, description="Publicação atualizada com sucesso")
     * )
     */
    public function update(UpdatePublicationRequest $request, int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            $data = $request->validated();
            $file = $request->file('file');

            $publication = $this->publicationService->updatePublication($publication, $data, $file);

            return response()->json([
                'success' => true,
                'message' => 'Publicação atualizada com sucesso.',
                'data' => $publication
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/publications/{id}",
     *     summary="Deletar publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Publicação deletada com sucesso")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            $this->publicationService->deletePublication($publication);

            return response()->json([
                'success' => true,
                'message' => 'Publicação deletada com sucesso.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/publications/{id}/upload",
     *     summary="Fazer upload de arquivo",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Arquivo enviado com sucesso")
     * )
     */
    public function uploadFile(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif|max:10240'
            ]);

            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            $file = $request->file('file');
            $publication = $this->publicationService->uploadFile($publication, $file);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso.',
                'data' => $publication
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/publications/{id}/file",
     *     summary="Remover arquivo da publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Arquivo removido com sucesso")
     * )
     */
    public function removeFile(int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            if (!$publication->hasFile()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não possui arquivo anexado.'
                ], 400);
            }

            $publication = $this->publicationService->removeFile($publication);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo removido com sucesso.',
                'data' => $publication
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover arquivo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/publications/{id}/download",
     *     summary="Baixar arquivo da publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Download do arquivo")
     * )
     */
    public function downloadFile(int $id)
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            if (!$publication->hasFile()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não possui arquivo anexado.'
                ], 400);
            }

            if (!Storage::exists($publication->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no servidor.'
                ], 404);
            }

            return Storage::download($publication->file_path, $publication->getFileName());

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar arquivo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/publications/search",
     *     summary="Buscar publicações",
     *     tags={"Publications"},
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Resultados da busca")
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:100'
            ]);

            $term = $request->input('q');
            $perPage = $request->input('per_page', 15);

            $publications = $this->publicationService->searchPublications($term, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $publications->items(),
                'search_term' => $term,
                'pagination' => [
                    'current_page' => $publications->currentPage(),
                    'last_page' => $publications->lastPage(),
                    'per_page' => $publications->perPage(),
                    'total' => $publications->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar publicações.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/publications/stats",
     *     summary="Estatísticas das publicações",
     *     tags={"Publications"},
     *     @OA\Response(response=200, description="Estatísticas das publicações")
     * )
     */
    public function stats(): JsonResponse
    {
        try {
            $statistics = $this->publicationService->getStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas recuperadas com sucesso.',
                'data' => $statistics
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/publications/status/{status}",
     *     summary="Publicações por status",
     *     tags={"Publications"},
     *     @OA\Parameter(name="status", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Publicações filtradas por status")
     * )
     */
    public function getByStatus(string $status, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            
            $publications = $this->publicationService->getPublicationsByStatus($status, $perPage);

            return response()->json([
                'success' => true,
                'message' => "Publicações com status '{$status}' recuperadas com sucesso.",
                'data' => $publications->items(),
                'status' => $status,
                'pagination' => [
                    'current_page' => $publications->currentPage(),
                    'last_page' => $publications->lastPage(),
                    'per_page' => $publications->perPage(),
                    'total' => $publications->total(),
                ]
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Status inválido.',
                'error' => $e->getMessage()
            ], 400);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar publicações por status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/publications/{id}/duplicate",
     *     summary="Duplicar publicação",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Publicação duplicada com sucesso")
     * )
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->findPublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            $duplicatedPublication = $this->publicationService->duplicatePublication($publication);

            return response()->json([
                'success' => true,
                'message' => 'Publicação duplicada com sucesso.',
                'data' => $duplicatedPublication
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/publications/{id}/restore",
     *     summary="Restaurar publicação deletada",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Publicação restaurada com sucesso")
     * )
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $publication = $this->publicationService->restorePublication($id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada ou não está deletada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Publicação restaurada com sucesso.',
                'data' => $publication
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar publicação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/publications/{id}/force",
     *     summary="Deletar publicação permanentemente",
     *     tags={"Publications"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Publicação deletada permanentemente")
     * )
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $result = $this->publicationService->forceDeletePublication($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publicação não encontrada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Publicação deletada permanentemente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar publicação permanentemente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
