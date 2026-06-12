<?php

namespace App\Services\University;

use App\Models\University\University;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Exception;

class UniversityService
{
    /**
     * Get paginated list of universities with optional filters
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $query = University::query()->with(['creator', 'updater']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Apply active filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Apply code filter
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        // Apply email filter
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Apply sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'code', 'email', 'is_active', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Store a new university
     */
    public function store(array $data): University
    {
        try {
            // Generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateUniversityCode($data['name']);
            }

            $university = University::create($data);

            return $university->load(['creator', 'updater']);
        } catch (Exception $e) {
            throw new Exception('Erro ao criar universidade: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific university
     */
    public function show(University $university): University
    {
        return $university->load(['creator', 'updater']);
    }

    /**
     * Update an existing university
     */
    public function update(University $university, array $data): University
    {
        try {
            $university->update($data);

            return $university->fresh(['creator', 'updater']);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar universidade: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete a university
     */
    public function destroy(University $university): bool
    {
        try {
            return $university->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar universidade: ' . $e->getMessage());
        }
    }

    /**
     * Get university statistics
     */
    public function getStats(): array
    {
        $total = University::count();
        $active = University::where('is_active', true)->count();
        $inactive = University::where('is_active', false)->count();
        $deleted = University::onlyTrashed()->count();
        $totalWithTrashed = University::withTrashed()->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'deleted' => $deleted,
            'total_with_trashed' => $totalWithTrashed,
            'recent' => University::latest()->take(5)->get(['id', 'name', 'code', 'created_at'])
        ];
    }

    /**
     * Search universities by term
     */
    public function search(string $term, int $perPage = 10): LengthAwarePaginator
    {
        return University::search($term)
            ->select(['id', 'name', 'code', 'email', 'is_active'])
            ->paginate($perPage);
    }

    /**
     * Restore a soft deleted university
     */
    public function restore(int $id): University
    {
        try {
            $university = University::onlyTrashed()->findOrFail($id);
            $university->restore();

            return $university->fresh(['creator', 'updater']);
        } catch (Exception $e) {
            throw new Exception('Erro ao restaurar universidade: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a university (permanent)
     */
    public function forceDestroy(int $id): bool
    {
        try {
            $university = University::withTrashed()->findOrFail($id);
            return $university->forceDelete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar permanentemente universidade: ' . $e->getMessage());
        }
    }

    /**
     * Toggle university active status
     */
    public function toggleStatus(University $university): University
    {
        try {
            $university->update([
                'is_active' => !$university->is_active
            ]);

            return $university->fresh(['creator', 'updater']);
        } catch (Exception $e) {
            throw new Exception('Erro ao alterar status da universidade: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a university
     */
    public function duplicate(University $university): University
    {
        try {
            $data = $university->toArray();
            
            // Remove unique fields and modify
            unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
            
            $data['name'] = $university->name . ' (Cópia)';
            $data['code'] = $this->generateUniversityCode($data['name']);
            
            return $this->store($data);
        } catch (Exception $e) {
            throw new Exception('Erro ao duplicar universidade: ' . $e->getMessage());
        }
    }

    /**
     * Get all active universities
     */
    public function getAllActive(): Collection
    {
        return University::active()
            ->select(['id', 'name', 'code', 'email'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get universities with trashed
     */
    public function getWithTrashed(Request $request): LengthAwarePaginator
    {
        $query = University::withTrashed()->with(['creator', 'updater']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Generate unique university code
     */
    private function generateUniversityCode(string $name): string
    {
        // Take first 3 letters of each word and convert to uppercase
        $words = explode(' ', $name);
        $code = '';
        
        foreach ($words as $word) {
            if (strlen($word) >= 3) {
                $code .= strtoupper(substr($word, 0, 3));
            } else {
                $code .= strtoupper($word);
            }
        }

        // Ensure uniqueness
        $originalCode = $code;
        $counter = 1;
        
        while (University::where('code', $code)->exists()) {
            $code = $originalCode . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Bulk update universities status
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        try {
            return University::whereIn('id', $ids)->update(['is_active' => $isActive]);
        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar universidades em lote: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete universities
     */
    public function bulkDelete(array $ids): int
    {
        try {
            return University::whereIn('id', $ids)->delete();
        } catch (Exception $e) {
            throw new Exception('Erro ao deletar universidades em lote: ' . $e->getMessage());
        }
    }
}