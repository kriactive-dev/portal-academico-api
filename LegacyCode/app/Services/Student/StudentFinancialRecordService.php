<?php

namespace App\Services\Student;

use App\Models\Student\StudentFinancialRecord;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentFinancialRecordImport;
use Carbon\Carbon;
use App\Services\Notification\NotificationService;
use App\Services\Notification\PushNotificationService;

class StudentFinancialRecordService
{

    protected $firebaseService;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService, PushNotificationService $firebaseService)
    {
        $this->notificationService = $notificationService;
        $this->firebaseService = $firebaseService;
    }
    /**
     * Get paginated list of financial records with optional filters
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = StudentFinancialRecord::query()
            ->with(['student', 'user', 'createdByUser', 'updatedByUser']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Apply student filter
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Apply student code filter
        if ($request->filled('student_code')) {
            $query->where('student_code', 'like', '%' . $request->student_code . '%');
        }

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Apply date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Apply amount range filter
        if ($request->filled('min_amount') && $request->filled('max_amount')) {
            $query->amountRange($request->min_amount, $request->max_amount);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['date', 'amount', 'status', 'student_code', 'payment_method', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Store a new financial record
     */
    public function store(array $data): StudentFinancialRecord
    {
        try {
            $record = StudentFinancialRecord::create($data);

            return $record->load(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao criar registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific financial record
     */
    public function show(StudentFinancialRecord $record): StudentFinancialRecord
    {
        return $record->load(['student', 'user', 'createdByUser', 'updatedByUser']);
    }

    /**
     * Update an existing financial record
     */
    public function update(StudentFinancialRecord $record, array $data): StudentFinancialRecord
    {
        try {
            $record->update($data);

            return $record->fresh(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete a financial record
     */
    public function destroy(StudentFinancialRecord $record): bool
    {
        try {
            return $record->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Get financial records statistics
     */
    public function getStats(): array
    {
        $total = StudentFinancialRecord::count();
        $totalAmount = StudentFinancialRecord::sum('amount');
        $avgAmount = StudentFinancialRecord::avg('amount');
        
        $byStatus = StudentFinancialRecord::selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get();

        $byPaymentMethod = StudentFinancialRecord::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();

        $monthlyData = StudentFinancialRecord::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as total_amount')
            ->whereYear('date', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $deleted = StudentFinancialRecord::onlyTrashed()->count();
        $totalWithTrashed = StudentFinancialRecord::withTrashed()->count();

        return [
            'total' => $total,
            'total_amount' => $totalAmount,
            'average_amount' => round($avgAmount, 2),
            'deleted' => $deleted,
            'total_with_trashed' => $totalWithTrashed,
            'by_status' => $byStatus,
            'by_payment_method' => $byPaymentMethod,
            'monthly_data' => $monthlyData,
            'recent' => StudentFinancialRecord::with('student')
                ->latest()
                ->take(5)
                ->get(['id', 'student_code', 'amount', 'status', 'date', 'student_id'])
        ];
    }

    /**
     * Search financial records by term
     */
    public function search(string $term, int $perPage = 10): LengthAwarePaginator
    {
        return StudentFinancialRecord::search($term)
            ->with(['student'])
            ->select(['id', 'student_code', 'amount', 'status', 'date', 'description', 'payment_method', 'student_id'])
            ->paginate($perPage);
    }

    /**
     * Restore a soft deleted financial record
     */
    public function restore(int $id): StudentFinancialRecord
    {
        try {
            $record = StudentFinancialRecord::onlyTrashed()->findOrFail($id);
            $record->restore();

            return $record->fresh(['student', 'user', 'createdByUser', 'updatedByUser']);
        } catch (Exception $e) {
            throw new Exception('Erro ao restaurar registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a financial record (permanent)
     */
    public function forceDestroy(int $id): bool
    {
        try {
            $record = StudentFinancialRecord::withTrashed()->findOrFail($id);
            return $record->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar permanentemente registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a financial record
     */
    public function duplicate(StudentFinancialRecord $record): StudentFinancialRecord
    {
        try {
            $data = $record->toArray();
            
            // Remove unique fields and modify
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            
            $data['description'] = ($data['description'] ?? '') . ' (Cópia)';
            $data['date'] = Carbon::now()->format('Y-m-d');
            
            return $this->store($data);
        } catch (Exception $e) {
            throw new Exception('Erro ao duplicar registro financeiro: ' . $e->getMessage());
        }
    }

    /**
     * Get financial records by student
     */
    public function getByStudent(int $studentId, Request $request): LengthAwarePaginator
    {
        $query = StudentFinancialRecord::where('student_id', $studentId)
            ->with(['student', 'createdByUser']);

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        return $query->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 15));
    }

    /**
     * Get financial records with trashed
     */
    public function getWithTrashed(Request $request): LengthAwarePaginator
    {
        $query = StudentFinancialRecord::withTrashed()
            ->with(['student', 'user', 'createdByUser', 'updatedByUser']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Bulk update financial records status
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        try {
            return StudentFinancialRecord::whereIn('id', $ids)->update(['status' => $status]);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar registros financeiros em lote: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete financial records
     */
    public function bulkDelete(array $ids): int
    {
        try {
            return StudentFinancialRecord::whereIn('id', $ids)->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar registros financeiros em lote: ' . $e->getMessage());
        }
    }

    /**
     * Import financial records from Excel file
     */
    public function importFromExcel($file): array
    {
        try {
            $import = new StudentFinancialRecordImport();
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
     * Get financial summary by student
     */
    public function getStudentSummary(int $studentId): array
    {
        $records = StudentFinancialRecord::where('student_id', $studentId);
        
        return [
            'total_records' => $records->count(),
            'total_amount' => $records->sum('amount'),
            'average_amount' => round($records->avg('amount'), 2),
            'by_status' => $records->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('status')
                ->get(),
            'last_record' => $records->latest('date')->first(['date', 'amount', 'status'])
        ];
    }
}