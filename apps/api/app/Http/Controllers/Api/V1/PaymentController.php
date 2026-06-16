<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Requests\Api\V1\UpdatePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reference_month')) {
            $query->where('reference_month', $request->reference_month);
        }

        $results = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * @param StorePaymentRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = Payment::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    /**
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PaymentResource($payment->load('student', 'course')),
        ]);
    }

    /**
     * @param UpdatePaymentRequest $request
     * @param Payment $payment
     * @return JsonResponse
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    /**
     * @param Payment $payment
     * @return JsonResponse
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);
        $payment->restore();

        return response()->json([
            'success' => true,
            'message' => 'Payment restored successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);
        $payment->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Payment permanently deleted.',
        ]);
    }

    /**
     * @param int $studentId
     * @return JsonResponse
     */
    public function getByStudent(int $studentId): JsonResponse
    {
        $student = Student::findOrFail($studentId);

        $payments = Payment::where('student_id', $studentId)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->paginate(request()->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentSummary(int $studentId): JsonResponse
    {
        $student = Student::findOrFail($studentId);

        $totalPaid = Payment::where('student_id', $studentId)
            ->where('status', 'paid')
            ->sum('amount');

        $totalPending = Payment::where('student_id', $studentId)
            ->where('status', 'pending')
            ->sum('amount');

        $totalOverdue = Payment::where('student_id', $studentId)
            ->where('status', 'overdue')
            ->sum('amount');

        $paymentsCount = Payment::where('student_id', $studentId)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'studentId' => $studentId,
                'studentName' => $student->name,
                'totalPaid' => (float) $totalPaid,
                'totalPending' => (float) $totalPending,
                'totalOverdue' => (float) $totalOverdue,
                'totalDebt' => (float) ($totalPending + $totalOverdue),
                'paymentsCount' => $paymentsCount,
            ],
        ]);
    }
}
