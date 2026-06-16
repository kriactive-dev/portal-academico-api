<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTrainerRequest;
use App\Http\Requests\Api\V1\UpdateTrainerRequest;
use App\Http\Resources\Api\V1\TrainerResource;
use App\Models\Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Trainer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => TrainerResource::collection($query->paginate($request->per_page ?? 15)),
        ]);
    }

    public function store(StoreTrainerRequest $request): JsonResponse
    {
        $trainer = Trainer::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Trainer created successfully.',
            'data' => new TrainerResource($trainer),
        ], 201);
    }

    public function show(Trainer $trainer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new TrainerResource($trainer->load('schoolClasses')),
        ]);
    }

    public function update(UpdateTrainerRequest $request, Trainer $trainer): JsonResponse
    {
        $trainer->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Trainer updated successfully.',
            'data' => new TrainerResource($trainer),
        ]);
    }

    public function destroy(Trainer $trainer): JsonResponse
    {
        $trainer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trainer deleted successfully.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $trainer = Trainer::onlyTrashed()->findOrFail($id);
        $trainer->restore();

        return response()->json([
            'success' => true,
            'message' => 'Trainer restored successfully.',
            'data' => new TrainerResource($trainer),
        ]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $trainer = Trainer::onlyTrashed()->findOrFail($id);
        $trainer->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Trainer permanently deleted.',
        ]);
    }

    public function toggleStatus(Trainer $trainer): JsonResponse
    {
        $trainer->update([
            'status' => $trainer->status === 'active' ? 'inactive' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trainer status toggled successfully.',
            'data' => new TrainerResource($trainer),
        ]);
    }
}
