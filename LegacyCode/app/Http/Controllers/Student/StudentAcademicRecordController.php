<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentAcademicRecordRequest;
use App\Http\Requests\Student\UpdateStudentAcademicRecordRequest;
use App\Models\Student\StudentAcademicRecord;
use App\Services\Student\StudentAcademicRecordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class StudentAcademicRecordController extends Controller
{
    protected StudentAcademicRecordService $academicRecordService;

    public function __construct(StudentAcademicRecordService $academicRecordService)
    {
        $this->academicRecordService = $academicRecordService;
    }

    /**
     * Display a listing of academic records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $records = $this->academicRecordService->index($request);

            return response()->json([
                'success' => true,
                'message' => 'Registros acadêmicos recuperados com sucesso.',
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
                'message' => 'Erro ao recuperar registros acadêmicos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created academic record.
     */
    public function store(StoreStudentAcademicRecordRequest $request): JsonResponse
    {
        try {
            $record = $this->academicRecordService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico criado com sucesso.',
                'data' => $record
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified academic record.
     */
    public function show(StudentAcademicRecord $academicRecord): JsonResponse
    {
        try {
            $record = $this->academicRecordService->show($academicRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico recuperado com sucesso.',
                'data' => $record
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified academic record.
     */
    public function update(UpdateStudentAcademicRecordRequest $request, StudentAcademicRecord $academicRecord): JsonResponse
    {
        try {
            $updatedRecord = $this->academicRecordService->update($academicRecord, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico atualizado com sucesso.',
                'data' => $updatedRecord
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified academic record (soft delete).
     */
    public function destroy(StudentAcademicRecord $academicRecord): JsonResponse
    {
        try {
            $this->academicRecordService->destroy($academicRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico deletado com sucesso.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic records statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->academicRecordService->getStats();

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
     * Search academic records.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $results = $this->academicRecordService->search(
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
     * Restore a soft deleted academic record.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $record = $this->academicRecordService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico restaurado com sucesso.',
                'data' => $record
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete an academic record (permanent).
     */
    public function forceDestroy(int $id): JsonResponse
    {
        try {
            $this->academicRecordService->forceDestroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico deletado permanentemente.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar permanentemente registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate an academic record.
     */
    public function duplicate(StudentAcademicRecord $academicRecord): JsonResponse
    {
        try {
            $newRecord = $this->academicRecordService->duplicate($academicRecord);

            return response()->json([
                'success' => true,
                'message' => 'Registro acadêmico duplicado com sucesso.',
                'data' => $newRecord
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar registro acadêmico.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic records by student.
     */
    public function getByStudent(int $studentId, Request $request): JsonResponse
    {
        try {
            $records = $this->academicRecordService->getByStudent($studentId, $request);

            return response()->json([
                'success' => true,
                'message' => 'Registros acadêmicos do estudante recuperados com sucesso.',
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
                'message' => 'Erro ao recuperar registros acadêmicos do estudante.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic records with trashed.
     */
    public function withTrashed(Request $request): JsonResponse
    {
        try {
            $records = $this->academicRecordService->getWithTrashed($request);

            return response()->json([
                'success' => true,
                'message' => 'Registros acadêmicos (incluindo deletados) recuperados com sucesso.',
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
                'message' => 'Erro ao recuperar registros acadêmicos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update academic records grade.
     */
    public function bulkUpdateGrade(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_academic_records,id',
            'grade' => 'required|string|max:20'
        ]);

        try {
            $updated = $this->academicRecordService->bulkUpdateGrade(
                $request->ids,
                $request->grade
            );

            return response()->json([
                'success' => true,
                'message' => "Nota de {$updated} registro(s) acadêmico(s) atualizada com sucesso.",
                'data' => ['updated_count' => $updated]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar notas dos registros acadêmicos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete academic records.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_academic_records,id'
        ]);

        try {
            $deleted = $this->academicRecordService->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} registro(s) acadêmico(s) deletado(s) com sucesso.",
                'data' => ['deleted_count' => $deleted]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar registros acadêmicos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import academic records from Excel.
     */
    public function importExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $result = $this->academicRecordService->importFromExcel($request->file('file'));

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
     * Get student academic summary.
     */
    public function getStudentSummary(int $studentId): JsonResponse
    {
        try {
            $summary = $this->academicRecordService->getStudentSummary($studentId);

            return response()->json([
                'success' => true,
                'message' => 'Resumo acadêmico do estudante recuperado com sucesso.',
                'data' => $summary
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar resumo acadêmico do estudante.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student transcript.
     */
    public function getStudentTranscript(int $studentId): JsonResponse
    {
        try {
            $transcript = $this->academicRecordService->getStudentTranscript($studentId);

            return response()->json([
                'success' => true,
                'message' => 'Histórico acadêmico do estudante recuperado com sucesso.',
                'data' => $transcript
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar histórico acadêmico do estudante.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
