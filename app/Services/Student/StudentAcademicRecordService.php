<?php

namespace App\Services\Student;

use App\Models\Student\StudentAcademicRecord;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentAcademicRecordImport;
use Carbon\Carbon;

class StudentAcademicRecordService
{
    /**
     * Get paginated list of academic records with optional filters
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = StudentAcademicRecord::query()
            ->with(['student', 'user', 'createdByUser', 'updatedByUser']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply academic year filter
        if ($request->filled('academic_year')) {
            $query->academicYear($request->academic_year);
        }

        // Apply semester filter
        if ($request->filled('semester')) {
            $query->semester($request->semester);
        }

        // Apply grade filter
        if ($request->filled('grade')) {
            $query->grade($request->grade);
        }

        // Apply subject filter
        if ($request->filled('subject_code')) {
            $query->subject($request->subject_code);
        }

        // Apply student filter
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Apply student code filter
        if ($request->filled('student_code')) {
            $query->where('student_code', 'like', '%' . $request->student_code . '%');
        }

        // Apply teacher filter
        if ($request->filled('teacher_name')) {
            $query->where('teacher_name', 'like', '%' . $request->teacher_name . '%');
        }

        // Apply sorting
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['date', 'academic_year', 'semester', 'subject_code', 'grade', 'student_code', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Store a new academic record
     */
    public function store(array $data): StudentAcademicRecord
    {
        try {
            $record = StudentAcademicRecord::create($data);

            return $record->load(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao criar registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific academic record
     */
    public function show(StudentAcademicRecord $record): StudentAcademicRecord
    {
        return $record->load(['student', 'user', 'createdByUser', 'updatedByUser']);
    }

    /**
     * Update an existing academic record
     */
    public function update(StudentAcademicRecord $record, array $data): StudentAcademicRecord
    {
        try {
            $record->update($data);

            return $record->fresh(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete an academic record
     */
    public function destroy(StudentAcademicRecord $record): bool
    {
        try {
            return $record->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Get academic records statistics
     */
    public function getStats(): array
    {
        $total = StudentAcademicRecord::count();
        
        $byAcademicYear = StudentAcademicRecord::selectRaw('academic_year, COUNT(*) as count')
            ->whereNotNull('academic_year')
            ->groupBy('academic_year')
            ->orderBy('academic_year', 'desc')
            ->get();

        $bySemester = StudentAcademicRecord::selectRaw('semester, COUNT(*) as count')
            ->whereNotNull('semester')
            ->groupBy('semester')
            ->get();

        $byGrade = StudentAcademicRecord::selectRaw('grade, COUNT(*) as count')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->get();

        $topSubjects = StudentAcademicRecord::selectRaw('subject_code, subject_name, COUNT(*) as count')
            ->whereNotNull('subject_code')
            ->groupBy('subject_code', 'subject_name')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        $deleted = StudentAcademicRecord::onlyTrashed()->count();
        $totalWithTrashed = StudentAcademicRecord::withTrashed()->count();

        return [
            'total' => $total,
            'deleted' => $deleted,
            'total_with_trashed' => $totalWithTrashed,
            'by_academic_year' => $byAcademicYear,
            'by_semester' => $bySemester,
            'by_grade' => $byGrade,
            'top_subjects' => $topSubjects,
            'recent' => StudentAcademicRecord::with('student')
                ->latest()
                ->take(5)
                ->get(['id', 'student_code', 'subject_code', 'subject_name', 'grade', 'date', 'student_id'])
        ];
    }

    /**
     * Search academic records by term
     */
    public function search(string $term, int $perPage = 10): LengthAwarePaginator
    {
        return StudentAcademicRecord::search($term)
            ->with(['student'])
            ->select(['id', 'student_code', 'subject_code', 'subject_name', 'grade', 'academic_year', 'semester', 'student_id'])
            ->paginate($perPage);
    }

    /**
     * Restore a soft deleted academic record
     */
    public function restore(int $id): StudentAcademicRecord
    {
        try {
            $record = StudentAcademicRecord::onlyTrashed()->findOrFail($id);
            $record->restore();

            return $record->fresh(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao restaurar registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Force delete an academic record (permanent)
     */
    public function forceDestroy(int $id): bool
    {
        try {
            $record = StudentAcademicRecord::withTrashed()->findOrFail($id);
            return $record->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar permanentemente registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate an academic record
     */
    public function duplicate(StudentAcademicRecord $record): StudentAcademicRecord
    {
        try {
            $data = $record->toArray();
            
            // Remove unique fields and modify
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            
            $data['description'] = ($data['description'] ?? '') . ' (Cópia)';
            $data['date'] = Carbon::now()->format('Y-m-d');
            
            return $this->store($data);
        } catch (Exception $e) {
            throw new Exception('Erro ao duplicar registro acadêmico: ' . $e->getMessage());
        }
    }

    /**
     * Get academic records by student
     */
    public function getByStudent(int $studentId, Request $request): LengthAwarePaginator
    {
        $query = StudentAcademicRecord::where('student_id', $studentId)
            ->with(['student', 'createdByUser']);

        if ($request->filled('academic_year')) {
            $query->academicYear($request->academic_year);
        }

        if ($request->filled('semester')) {
            $query->semester($request->semester);
        }

        return $query->orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 15));
    }

    /**
     * Get academic records with trashed
     */
    public function getWithTrashed(Request $request): LengthAwarePaginator
    {
        $query = StudentAcademicRecord::withTrashed()
            ->with(['student', 'user', 'createdByUser', 'updatedByUser']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Bulk update academic records
     */
    public function bulkUpdateGrade(array $ids, string $grade): int
    {
        try {
            return StudentAcademicRecord::whereIn('id', $ids)->update(['grade' => $grade]);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar registros acadêmicos em lote: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete academic records
     */
    public function bulkDelete(array $ids): int
    {
        try {
            return StudentAcademicRecord::whereIn('id', $ids)->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar registros acadêmicos em lote: ' . $e->getMessage());
        }
    }

    /**
     * Import academic records from Excel file
     */
    public function importFromExcel($file): array
    {
        try {
            $import = new StudentAcademicRecordImport();
            Excel::import($import, $file);

            return [
                'success' => true,
                'imported_count' => $import->getImportedCount(),
                'errors' => $import->getErrors()
            ];
        } catch (Exception $e) {
            throw new Exception('Erro ao importar arquivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Get academic summary by student
     */
    public function getStudentSummary(int $studentId): array
    {
        $records = StudentAcademicRecord::where('student_id', $studentId);
        
        return [
            'total_records' => $records->count(),
            'total_credits' => $records->sum('credits'),
            'by_academic_year' => $records->selectRaw('academic_year, COUNT(*) as count')
                ->whereNotNull('academic_year')
                ->groupBy('academic_year')
                ->get(),
            'by_grade' => $records->selectRaw('grade, COUNT(*) as count')
                ->whereNotNull('grade')
                ->groupBy('grade')
                ->get(),
            'last_record' => $records->latest('date')->first(['date', 'subject_name', 'grade'])
        ];
    }

    /**
     * Get student transcript
     */
    public function getStudentTranscript(int $studentId): array
    {
        $records = StudentAcademicRecord::where('student_id', $studentId)
            ->with('student')
            ->orderBy('academic_year')
            ->orderBy('semester')
            ->orderBy('subject_code')
            ->get();

        $groupedByYear = $records->groupBy('academic_year');
        
        return [
            'student' => $records->first()->student ?? null,
            'records_by_year' => $groupedByYear,
            'total_credits' => $records->sum('credits'),
            'total_subjects' => $records->count()
        ];
    }
}