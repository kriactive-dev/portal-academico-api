<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCourseRequest;
use App\Http\Requests\Api\V1\UpdateCourseRequest;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $courses = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function allActive(): JsonResponse
    {
        $courses = Course::where('is_active', true)->get(['id', 'name', 'is_active']);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * @param StoreCourseRequest $request
     * @return JsonResponse
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = Course::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'data' => new CourseResource($course),
        ], 201);
    }

    /**
     * @param Course $course
     * @return JsonResponse
     */
    public function show(Course $course): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new CourseResource($course->load('schoolClasses', 'fees')),
        ]);
    }

    /**
     * @param UpdateCourseRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'data' => new CourseResource($course),
        ]);
    }

    /**
     * @param Course $course
     * @return JsonResponse
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.',
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return response()->json([
            'success' => true,
            'message' => 'Course restored successfully.',
            'data' => new CourseResource($course),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Course permanently deleted.',
        ]);
    }

    /**
     * @param Course $course
     * @return JsonResponse
     */
    public function duplicate(Course $course): JsonResponse
    {
        $newCourse = $course->replicate();
        $newCourse->name = $course->name . ' (copy)';
        $newCourse->save();

        return response()->json([
            'success' => true,
            'message' => 'Course duplicated successfully.',
            'data' => new CourseResource($newCourse),
        ], 201);
    }
}
