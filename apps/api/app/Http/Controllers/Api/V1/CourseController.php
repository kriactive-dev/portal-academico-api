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
    public function index(Request $request): JsonResponse
    {
        $query = Course::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($query->paginate($request->per_page ?? 15)),
        ]);
    }

    public function allActive(): JsonResponse
    {
        $courses = Course::where('is_active', true)->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = Course::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'data' => new CourseResource($course),
        ], 201);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new CourseResource($course->load('schoolClasses', 'fees')),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'data' => new CourseResource($course),
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.',
        ]);
    }

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

    public function forceDelete(int $id): JsonResponse
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Course permanently deleted.',
        ]);
    }

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
