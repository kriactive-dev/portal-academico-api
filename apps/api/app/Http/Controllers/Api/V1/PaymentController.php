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

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($query->paginate($request->per_page ?? 15)),
        ]);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = Payment::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PaymentResource($payment->load('student', 'course')),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.',
        ]);
    }

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

    public function forceDelete(int $id): JsonResponse
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);
        $payment->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Payment permanently deleted.',
        ]);
    }

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
        ]);
    }

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
                'student_id' => $studentId,
                'student_name' => $student->name,
                'total_paid' => (float) $totalPaid,
                'total_pending' => (float) $totalPending,
                'total_overdue' => (float) $totalOverdue,
                'total_debt' => (float) ($totalPending + $totalOverdue),
                'payments_count' => $paymentsCount,
            ],
        ]);
    }
}
