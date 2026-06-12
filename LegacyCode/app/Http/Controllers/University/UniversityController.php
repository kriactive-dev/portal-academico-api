<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Http\Requests\University\StoreUniversityRequest;
use App\Http\Requests\University\UpdateUniversityRequest;
use App\Models\University\University;
use App\Services\University\UniversityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class UniversityController extends Controller
{
    protected UniversityService $universityService;

    public function __construct(UniversityService $universityService)
    {
        $this->universityService = $universityService;
    }

    /**
     * Display a listing of universities.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $universities = $this->universityService->index($request);

            return response()->json([
                'success' => true,
                'message' => 'Universidades recuperadas com sucesso.',
                'data' => $universities->items(),
                'pagination' => [
                    'current_page' => $universities->currentPage(),
                    'last_page' => $universities->lastPage(),
                    'per_page' => $universities->perPage(),
                    'total' => $universities->total(),
                    'from' => $universities->firstItem(),
                    'to' => $universities->lastItem(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar universidades.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created university in storage.
     */
    public function store(StoreUniversityRequest $request): JsonResponse
    {
        try {
            $university = $this->universityService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Universidade criada com sucesso.',
                'data' => $university
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified university.
     */
    public function show(University $university): JsonResponse
    {
        try {
            $university = $this->universityService->show($university);

            return response()->json([
                'success' => true,
                'message' => 'Universidade recuperada com sucesso.',
                'data' => $university
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified university in storage.
     */
    public function update(UpdateUniversityRequest $request, University $university): JsonResponse
    {
        try {
            $updatedUniversity = $this->universityService->update($university, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Universidade atualizada com sucesso.',
                'data' => $updatedUniversity
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified university from storage (soft delete).
     */
    public function destroy(University $university): JsonResponse
    {
        try {
            $this->universityService->destroy($university);

            return response()->json([
                'success' => true,
                'message' => 'Universidade deletada com sucesso.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get university statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->universityService->getStats();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas recuperadas com sucesso.',
                'data' => $stats
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search universities.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $results = $this->universityService->search(
                $request->term,
                $request->get('per_page', 10)
            );

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar busca.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted university.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $university = $this->universityService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Universidade restaurada com sucesso.',
                'data' => $university
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a university (permanent).
     */
    public function forceDestroy(int $id): JsonResponse
    {
        try {
            $this->universityService->forceDestroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Universidade deletada permanentemente.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar permanentemente universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle university active status.
     */
    public function toggleStatus(University $university): JsonResponse
    {
        try {
            $updatedUniversity = $this->universityService->toggleStatus($university);

            return response()->json([
                'success' => true,
                'message' => 'Status da universidade alterado com sucesso.',
                'data' => $updatedUniversity
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status da universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a university.
     */
    public function duplicate(University $university): JsonResponse
    {
        try {
            $newUniversity = $this->universityService->duplicate($university);

            return response()->json([
                'success' => true,
                'message' => 'Universidade duplicada com sucesso.',
                'data' => $newUniversity
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar universidade.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active universities.
     */
    public function getAllActive(): JsonResponse
    {
        try {
            $universities = $this->universityService->getAllActive();

            return response()->json([
                'success' => true,
                'message' => 'Universidades ativas recuperadas com sucesso.',
                'data' => $universities
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar universidades ativas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get universities with trashed.
     */
    public function withTrashed(Request $request): JsonResponse
    {
        try {
            $universities = $this->universityService->getWithTrashed($request);

            return response()->json([
                'success' => true,
                'message' => 'Universidades (incluindo deletadas) recuperadas com sucesso.',
                'data' => $universities->items(),
                'pagination' => [
                    'current_page' => $universities->currentPage(),
                    'last_page' => $universities->lastPage(),
                    'per_page' => $universities->perPage(),
                    'total' => $universities->total(),
                    'from' => $universities->firstItem(),
                    'to' => $universities->lastItem(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar universidades.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update universities status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:universities,id',
            'is_active' => 'required|boolean'
        ]);

        try {
            $updated = $this->universityService->bulkUpdateStatus(
                $request->ids,
                $request->is_active
            );

            return response()->json([
                'success' => true,
                'message' => "Status de {$updated} universidade(s) atualizado com sucesso.",
                'data' => ['updated_count' => $updated]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status das universidades.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete universities.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:universities,id'
        ]);

        try {
            $deleted = $this->universityService->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} universidade(s) deletada(s) com sucesso.",
                'data' => ['deleted_count' => $deleted]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar universidades.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
