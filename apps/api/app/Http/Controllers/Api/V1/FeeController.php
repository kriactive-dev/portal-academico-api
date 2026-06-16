<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFeeRequest;
use App\Http\Requests\Api\V1\UpdateFeeRequest;
use App\Http\Resources\Api\V1\FeeResource;
use App\Models\Fee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Fee::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $results = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => FeeResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * @param StoreFeeRequest $request
     * @return JsonResponse
     */
    public function store(StoreFeeRequest $request): JsonResponse
    {
        $fee = Fee::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Fee created successfully.',
            'data' => new FeeResource($fee),
        ], 201);
    }

    /**
     * @param Fee $fee
     * @return JsonResponse
     */
    public function show(Fee $fee): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new FeeResource($fee->load('course')),
        ]);
    }

    /**
     * @param UpdateFeeRequest $request
     * @param Fee $fee
     * @return JsonResponse
     */
    public function update(UpdateFeeRequest $request, Fee $fee): JsonResponse
    {
        $fee->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Fee updated successfully.',
            'data' => new FeeResource($fee),
        ]);
    }

    /**
     * @param Fee $fee
     * @return JsonResponse
     */
    public function destroy(Fee $fee): JsonResponse
    {
        $fee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee deleted successfully.',
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $fee = Fee::onlyTrashed()->findOrFail($id);
        $fee->restore();

        return response()->json([
            'success' => true,
            'message' => 'Fee restored successfully.',
            'data' => new FeeResource($fee),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $fee = Fee::onlyTrashed()->findOrFail($id);
        $fee->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Fee permanently deleted.',
        ]);
    }
}
