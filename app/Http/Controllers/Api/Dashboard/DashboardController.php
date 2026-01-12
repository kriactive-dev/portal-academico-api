<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Obter métricas do dashboard
     * 
     * @return JsonResponse
     */
    public function getMetrics(): JsonResponse
    {
        try {
            $metrics = $this->dashboardService->getMetrics();

            return response()->json([
                'success' => true,
                'message' => 'Métricas do dashboard obtidas com sucesso.',
                'data' => $metrics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}