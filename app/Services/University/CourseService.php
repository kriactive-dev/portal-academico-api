<?php

namespace App\Services\University;

use App\Models\University\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class CourseService
{
    /**
     * Listar cursos com filtros e paginação
     */
    public function listCourses(array $filters = []): LengthAwarePaginator
    {
        $query = Course::with(['university', 'creator', 'updater'])
            ->orderBy('name', 'asc');

        // Aplicar filtros
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['university_id']) && !empty($filters['university_id'])) {
            $query->byUniversity($filters['university_id']);
        }

        if (isset($filters['course_code']) && !empty($filters['course_code'])) {
            $query->where('course_code', 'LIKE', "%{$filters['course_code']}%");
        }

        if (isset($filters['duration']) && !empty($filters['duration'])) {
            $query->where('duration', 'LIKE', "%{$filters['duration']}%");
        }

        if (isset($filters['responsible']) && !empty($filters['responsible'])) {
            $query->where('responsible', 'LIKE', "%{$filters['responsible']}%");
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * Buscar curso por ID
     */
    public function findCourse(int $id): ?Course
    {
        return Course::with(['university', 'creator', 'updater', 'deleter'])->find($id);
    }

    /**
     * Criar novo curso
     */
    public function createCourse(array $data): Course
    {
        try {
            DB::beginTransaction();

            $course = Course::create($data);

            DB::commit();

            return $course->fresh(['university', 'creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualizar curso
     */
    public function updateCourse(Course $course, array $data): Course
    {
        try {
            DB::beginTransaction();

            $course->update($data);

            DB::commit();

            return $course->fresh(['university', 'creator', 'updater']);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar curso (soft delete)
     */
    public function deleteCourse(Course $course): bool
    {
        return $course->delete();
    }

    /**
     * Restaurar curso deletado
     */
    public function restoreCourse(int $id): ?Course
    {
        $course = Course::withTrashed()->find($id);
        
        if ($course && $course->trashed()) {
            $course->restore();
            return $course->fresh(['university', 'creator', 'updater']);
        }

        return null;
    }

    /**
     * Deletar curso permanentemente
     */
    public function forceDeleteCourse(int $id): bool
    {
        $course = Course::withTrashed()->find($id);
        
        if ($course) {
            return $course->forceDelete();
        }

        return false;
    }

    /**
     * Obter estatísticas dos cursos
     */
    public function getStatistics(): array
    {
        $total = Course::count();
        $totalWithTrashed = Course::withTrashed()->count();
        $deleted = $totalWithTrashed - $total;
        $byUniversity = Course::select('university_id', DB::raw('count(*) as total'))
            ->with('university:id,name')
            ->whereNotNull('university_id')
            ->groupBy('university_id')
            ->get();

        return [
            'total' => $total,
            'deleted' => $deleted,
            'by_university' => $byUniversity,
            'total_with_trashed' => $totalWithTrashed
        ];
    }

    /**
     * Buscar cursos por universidade
     */
    public function getCoursesByUniversity(int $universityId, int $perPage = 15): LengthAwarePaginator
    {
        return Course::with(['creator', 'updater'])
            ->byUniversity($universityId)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Buscar cursos por termo
     */
    public function searchCourses(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Course::with(['university', 'creator', 'updater'])
            ->search($term)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Obter todos os cursos ativos
     */
    public function getAllActiveCourses(): Collection
    {
        return Course::with(['university'])
            ->active()
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Duplicar curso
     */
    public function duplicateCourse(Course $course): Course
    {
        $data = $course->toArray();
        
        // Remover campos que não devem ser duplicados
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        unset($data['created_by_user_id'], $data['updated_by_user_id'], $data['deleted_by_user_id']);
        
        // Adicionar sufixo ao nome e código
        $data['name'] = $data['name'] . ' (Cópia)';
        if ($data['course_code']) {
            $data['course_code'] = $data['course_code'] . '_COPY';
        }

        return Course::create($data);
    }

    /**
     * Validar se código do curso é único
     */
    public function isCourseCodeUnique(string $courseCode, ?int $excludeId = null): bool
    {
        $query = Course::where('course_code', $courseCode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    /**
     * Obter cursos por duração
     */
    public function getCoursesByDuration(string $duration, int $perPage = 15): LengthAwarePaginator
    {
        return Course::with(['university', 'creator', 'updater'])
            ->where('duration', 'LIKE', "%{$duration}%")
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Obter cursos sem universidade
     */
    public function getCoursesWithoutUniversity(int $perPage = 15): LengthAwarePaginator
    {
        return Course::with(['creator', 'updater'])
            ->whereNull('university_id')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }
}