<?php

namespace App\Http\Controllers\Api\ExternalApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExternalApp\StoreExternalAppRequest;
use App\Http\Requests\ExternalApp\UpdateExternalAppRequest;
use App\Services\ExternalApp\ExternalAppService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalAppController extends Controller
{
    protected ExternalAppService $externalAppService;

    public function __construct(ExternalAppService $externalAppService)
    {
        $this->externalAppService = $externalAppService;
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'university_id', 'course_code', 'duration', 
                'responsible', 'per_page'
            ]);

            $externalApps = $this->externalAppService->listExternalApps($filters);

            return response()->json([
                'success' => true,
                'message' => 'aplicações externas recuperados com sucesso.',
                'data' => $externalApps->items(),
                'pagination' => [
                    'current_page' => $externalApps->currentPage(),
                    'last_page' => $externalApps->lastPage(),
                    'per_page' => $externalApps->perPage(),
                    'total' => $externalApps->total(),
                    'from' => $externalApps->firstItem(),
                    'to' => $externalApps->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar aplicações externas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(StoreExternalAppRequest $request): JsonResponse
    {
        try {
            $externalApp = $this->externalAppService->createExternalApp($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa criado com sucesso.',
                'data' => $externalApp
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $course = $this->externalAppService->findExternalApp($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicação externa não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa recuperado com sucesso.',
                'data' => $course
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(UpdateExternalAppRequest $request, int $id): JsonResponse
    {
        try {
            $externalApp = $this->externalAppService->findExternalApp($id);

            if (!$externalApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicativo externo não encontrado.'
                ], 404);
            }

            $updatedExternalApp = $this->externalAppService->updateExternalApp($externalApp, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Aplicativo externo atualizado com sucesso.',
                'data' => $updatedExternalApp
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $externalApp = $this->externalAppService->findExternalApp($id);

            if (!$externalApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicativo externo não encontrado.'
                ], 404);
            }

            $this->externalAppService->deleteExternalApp($externalApp);

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa deletado com sucesso.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $stats = $this->externalAppService->getStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas recuperadas com sucesso.',
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'term' => 'required|string|min:1'
            ]);

            $perPage = $request->input('per_page', 15);
            $externalApps = $this->externalAppService->searchExternalApps($request->input('term'), $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $externalApps->items(),
                'pagination' => [
                    'current_page' => $externalApps->currentPage(),
                    'last_page' => $externalApps->lastPage(),
                    'per_page' => $externalApps->perPage(),
                    'total' => $externalApps->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar aplicações externas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function restore(int $id): JsonResponse
    {
        try {
            $externalApp = $this->externalAppService->restoreExternalApp($id);

            if (!$externalApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicação externa não encontrado ou não estava deletado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa restaurado com sucesso.',
                'data' => $externalApp
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->externalAppService->forceDeleteExternalApp($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicativo externo não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa deletado permanentemente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar Aplicação externa permanentemente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function duplicate(int $id): JsonResponse
    {
        try {
            $externalApp = $this->externalAppService->findExternalApp($id);

            if (!$externalApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aplicação externa não encontrado.'
                ], 404);
            }

            $duplicatedExternalApp = $this->externalAppService->duplicateExternalApp($externalApp);

            return response()->json([
                'success' => true,
                'message' => 'Aplicação externa duplicado com sucesso.',
                'data' => $duplicatedExternalApp->fresh(['creator', 'updater'])
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar Aplicação externa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
