<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentFinancialRecordRequest;
use App\Http\Requests\Student\UpdateStudentFinancialRecordRequest;
use App\Models\Student\StudentFinancialRecord;
use App\Services\Student\StudentFinancialRecordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class StudentFinancialRecordController extends Controller
{
    protected StudentFinancialRecordService $financialRecordService;

    public function __construct(StudentFinancialRecordService $financialRecordService)
    {
        $this->financialRecordService = $financialRecordService;
    }

    /**
     * Display a listing of financial records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $records = $this->financialRecordService->index($request);

            return response()->json([
                'success' => true,
                'message' => 'Registros financeiros recuperados com sucesso.',
                'data' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                    'from' => $records->firstItem(),
                    'to' => $records->lastItem(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar registros financeiros.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created financial record.
     */
    public function store(StoreStudentFinancialRecordRequest $request): JsonResponse
    {
        try {
            $record = $this->financialRecordService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro criado com sucesso.',
                'data' => $record
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified financial record.
     */
    public function show(StudentFinancialRecord $financialRecord): JsonResponse
    {
        try {
            $record = $this->financialRecordService->show($financialRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro recuperado com sucesso.',
                'data' => $record
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified financial record.
     */
    public function update(UpdateStudentFinancialRecordRequest $request, StudentFinancialRecord $financialRecord): JsonResponse
    {
        try {
            $updatedRecord = $this->financialRecordService->update($financialRecord, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro atualizado com sucesso.',
                'data' => $updatedRecord
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified financial record (soft delete).
     */
    public function destroy(StudentFinancialRecord $financialRecord): JsonResponse
    {
        try {
            $this->financialRecordService->destroy($financialRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro deletado com sucesso.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial records statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->financialRecordService->getStats();

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
     * Search financial records.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $results = $this->financialRecordService->search(
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
     * Restore a soft deleted financial record.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $record = $this->financialRecordService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro restaurado com sucesso.',
                'data' => $record
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a financial record (permanent).
     */
    public function forceDestroy(int $id): JsonResponse
    {
        try {
            $this->financialRecordService->forceDestroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro deletado permanentemente.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar permanentemente registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a financial record.
     */
    public function duplicate(StudentFinancialRecord $financialRecord): JsonResponse
    {
        try {
            $newRecord = $this->financialRecordService->duplicate($financialRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro financeiro duplicado com sucesso.',
                'data' => $newRecord
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar registro financeiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial records by student.
     */
    public function getByStudent(int $studentId, Request $request): JsonResponse
    {
        try {
            $records = $this->financialRecordService->getByStudent($studentId, $request);

            return response()->json([
                'success' => true,
                'message' => 'Registros financeiros do estudante recuperados com sucesso.',
                'data' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar registros financeiros do estudante.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial records with trashed.
     */
    public function withTrashed(Request $request): JsonResponse
    {
        try {
            $records = $this->financialRecordService->getWithTrashed($request);

            return response()->json([
                'success' => true,
                'message' => 'Registros financeiros (incluindo deletados) recuperados com sucesso.',
                'data' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar registros financeiros.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update financial records status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_financial_records,id',
            'status' => 'required|string|max:50'
        ]);

        try {
            $updated = $this->financialRecordService->bulkUpdateStatus(
                $request->ids,
                $request->status
            );

            return response()->json([
                'success' => true,
                'message' => "Status de {$updated} registro(s) financeiro(s) atualizado com sucesso.",
                'data' => ['updated_count' => $updated]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status dos registros financeiros.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete financial records.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_financial_records,id'
        ]);

        try {
            $deleted = $this->financialRecordService->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} registro(s) financeiro(s) deletado(s) com sucesso.",
                'data' => ['deleted_count' => $deleted]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar registros financeiros.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import financial records from Excel.
     */
    public function importExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $result = $this->financialRecordService->importFromExcel($request->file('file'));

            return response()->json([
                'success' => $result['success'],
                'message' => 'Importação processada com sucesso.',
                'data' => [
                    'imported_count' => $result['imported_count'],
                    'errors' => $result['errors']
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar arquivo Excel.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student financial summary.
     */
    public function getStudentSummary(int $studentId): JsonResponse
    {
        try {
            $summary = $this->financialRecordService->getStudentSummary($studentId);

            return response()->json([
                'success' => true,
                'message' => 'Resumo financeiro do estudante recuperado com sucesso.',
                'data' => $summary
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar resumo financeiro do estudante.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
