<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreStudentRequest;
use App\Http\Requests\Api\V1\UpdateStudentRequest;
use App\Http\Resources\Api\V1\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($query->paginate($request->per_page ?? 15)),
        ]);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = Student::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new StudentResource($student->load('schoolClasses', 'payments')),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();

        return response()->json([
            'success' => true,
            'message' => 'Student restored successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Student permanently deleted.',
        ]);
    }

    public function toggleStatus(Student $student): JsonResponse
    {
        $student->update([
            'status' => $student->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student status toggled successfully.',
            'data' => new StudentResource($student),
        ]);
    }
}
