<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DenyRequestEntryRequest;
use App\Http\Requests\Api\V1\StoreRequestEntryRequest;
use App\Http\Requests\Api\V1\UpdateRequestEntryRequest;
use App\Http\Resources\Api\V1\RequestResource;
use App\Models\RequestEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestEntryController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = RequestEntry::query();

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => RequestResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * @param StoreRequestEntryRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequestEntryRequest $request): JsonResponse
    {
        $requestEntry = RequestEntry::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Request created successfully.',
            'data' => new RequestResource($requestEntry),
        ], 201);
    }

    /**
     * @param RequestEntry $requestEntry
     * @return JsonResponse
     */
    public function show(RequestEntry $requestEntry): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new RequestResource($requestEntry->load('student')),
        ]);
    }

    /**
     * @param UpdateRequestEntryRequest $request
     * @param RequestEntry $requestEntry
     * @return JsonResponse
     */
    public function update(UpdateRequestEntryRequest $request, RequestEntry $requestEntry): JsonResponse
    {
        $requestEntry->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully.',
            'data' => new RequestResource($requestEntry),
        ]);
    }

    /**
     * @param RequestEntry $requestEntry
     * @return JsonResponse
     */
    public function destroy(RequestEntry $requestEntry): JsonResponse
    {
        $requestEntry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully.',
        ]);
    }

    /**
     * @param RequestEntry $requestEntry
     * @return JsonResponse
     */
    public function approve(RequestEntry $requestEntry): JsonResponse
    {
        $requestEntry->update([
            'status' => 'approved',
            'response_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully.',
            'data' => new RequestResource($requestEntry),
        ]);
    }

    /**
     * @param DenyRequestEntryRequest $request
     * @param RequestEntry $requestEntry
     * @return JsonResponse
     */
    public function deny(DenyRequestEntryRequest $request, RequestEntry $requestEntry): JsonResponse
    {
        $requestEntry->update([
            'status' => 'denied',
            'denial_reason' => $request->input('denial_reason'),
            'response_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request denied.',
            'data' => new RequestResource($requestEntry),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $requestEntry = RequestEntry::onlyTrashed()->findOrFail($id);
        $requestEntry->restore();

        return response()->json([
            'success' => true,
            'message' => 'Request restored successfully.',
            'data' => new RequestResource($requestEntry),
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        $requestEntry = RequestEntry::onlyTrashed()->findOrFail($id);
        $requestEntry->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Request permanently deleted.',
        ]);
    }
}
