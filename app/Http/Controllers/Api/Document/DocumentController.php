<?php

namespace App\Http\Controllers\Api\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Services\Document\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'status_id', 'user_id', 'file_type', 
                'due_date_filter', 'due_days', 'date_from', 'date_to', 
                'status', 'per_page'
            ]);

            $documents = $this->documentService->index($filters);

            return response()->json([
                'success' => true,
                'message' => 'Documentos listados com sucesso',
                'data' => $documents,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar documentos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();

            $document = $this->documentService->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Documento criado com sucesso',
                'data' => $document,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $document = $this->documentService->show($id);

            return response()->json([
                'success' => true,
                'message' => 'Documento encontrado',
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['updated_by_user_id'] = auth()->id();

            $document = $this->documentService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Documento atualizado com sucesso',
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->documentService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Documento deletado com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a soft deleted document
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $document = $this->documentService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Documento restaurado com sucesso',
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete a document
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->documentService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Documento deletado permanentemente com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar documento permanentemente: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload files to a document
     */
    public function uploadFiles(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|max:10240', // 10MB max per file
            ]);

            $document = $this->documentService->show($id);
            $uploadedFiles = [];

            foreach ($request->file('files') as $file) {
                $documentFile = $this->documentService->uploadDocumentFile($document, $file);
                $uploadedFiles[] = $documentFile;
            }

            return response()->json([
                'success' => true,
                'message' => 'Arquivos enviados com sucesso',
                'data' => $uploadedFiles,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific file from a document
     */
    public function deleteFile(int $id, int $fileId): JsonResponse
    {
        try {
            $this->documentService->deleteDocumentFile($id, $fileId);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo deletado com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar arquivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a specific file from a document
     */
    public function downloadFile(int $id, int $fileId): StreamedResponse|JsonResponse
    {
        try {
            $documentFile = \App\Models\Documents\DocumentFile::where('document_id', $id)
                                                              ->where('id', $fileId)
                                                              ->firstOrFail();

            if (!Storage::disk('public')->exists($documentFile->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado',
                ], 404);
            }

            return Storage::disk('public')->download(
                $documentFile->file_path,
                $documentFile->original_name
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer download do arquivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['user_id', 'date_from', 'date_to']);
            $stats = $this->documentService->getStats($filters);

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas obtidas com sucesso',
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search documents
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
            ]);

            $filters = $request->only(['status_id', 'user_id']);
            $documents = $this->documentService->search($request->q, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso',
                'data' => $documents,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na busca: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get documents by status
     */
    public function getByStatus(int $statusId, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['user_id']);
            $documents = $this->documentService->getByStatus($statusId, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Documentos por status obtidos com sucesso',
                'data' => $documents,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter documentos por status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change document status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status_id' => 'required|integer|exists:document_statuses,id',
                'comments' => 'nullable|string|max:1000',
            ]);

            $document = $this->documentService->changeStatus(
                $id,
                $request->status_id,
                $request->comments
            );

            return response()->json([
                'success' => true,
                'message' => 'Status do documento alterado com sucesso',
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status do documento: ' . $e->getMessage(),
            ], 500);
        }
    }
}
