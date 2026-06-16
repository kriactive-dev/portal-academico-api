<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSchoolClassRequest;
use App\Http\Requests\Api\V1\UpdateSchoolClassRequest;
use App\Http\Resources\Api\V1\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchoolClass::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $results = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => SchoolClassResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * @param StoreSchoolClassRequest $request
     * @return JsonResponse
     */
    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        $trainerIds = $request->input('trainer_ids') ?? $request->input('trainerIds');
        if ($trainerIds) {
            $schoolClass->trainers()->sync($trainerIds);
        }

        $studentIds = $request->input('student_ids') ?? $request->input('studentIds');
        if ($studentIds) {
            $schoolClass->students()->sync($studentIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'School class created successfully.',
            'data' => new SchoolClassResource($schoolClass->load('course', 'trainers', 'students')),
        ], 201);
    }

    /**
     * @param SchoolClass $schoolClass
     * @return JsonResponse
     */
    public function show(SchoolClass $schoolClass): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new SchoolClassResource($schoolClass->load('course', 'trainers', 'students')),
        ]);
    }

    /**
     * @param UpdateSchoolClassRequest $request
     * @param SchoolClass $schoolClass
     * @return JsonResponse
     */
    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): JsonResponse
    {
        $schoolClass->update($request->validated());

        $trainerIds = $request->input('trainer_ids') ?? $request->input('trainerIds');
        if ($trainerIds) {
            $schoolClass->trainers()->sync($trainerIds);
        }

        $studentIds = $request->input('student_ids') ?? $request->input('studentIds');
        if ($studentIds) {
            $schoolClass->students()->sync($studentIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'School class updated successfully.',
            'data' => new SchoolClassResource($schoolClass->load('course', 'trainers', 'students')),
        ]);
    }

    /**
     * @param SchoolClass $schoolClass
     * @return JsonResponse
     */
    public function destroy(SchoolClass $schoolClass): JsonResponse
    {
        $schoolClass->delete();

        return response()->json([
            'success' => true,
            'message' => 'School class deleted successfully.',
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $schoolClass = SchoolClass::onlyTrashed()->findOrFail($id);
        $schoolClass->restore();

        return response()->json([
            'success' => true,
            'message' => 'School class restored successfully.',
            'data' => new SchoolClassResource($schoolClass->load('course', 'trainers', 'students')),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $schoolClass = SchoolClass::onlyTrashed()->findOrFail($id);
        $schoolClass->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'School class permanently deleted.',
        ]);
    }
}
