<?php

namespace App\Http\Controllers\Api\University;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\University\Course;
use App\Services\University\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * @OA\Get(
     *     path="/api/courses",
     *     summary="Listar cursos",
     *     tags={"Courses"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="university_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="course_code", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="duration", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista de cursos")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'university_id', 'course_code', 'duration', 
                'responsible', 'per_page'
            ]);

            $courses = $this->courseService->listCourses($filters);

            return response()->json([
                'success' => true,
                'message' => 'Cursos recuperados com sucesso.',
                'data' => $courses->items(),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                    'from' => $courses->firstItem(),
                    'to' => $courses->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar cursos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/courses",
     *     summary="Criar curso",
     *     tags={"Courses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="course_code", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="duration", type="string"),
     *             @OA\Property(property="responsible", type="string"),
     *             @OA\Property(property="university_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Curso criado com sucesso")
     * )
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        try {
            $course = $this->courseService->createCourse($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Curso criado com sucesso.',
                'data' => $course
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/courses/{id}",
     *     summary="Obter curso",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Curso encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $course = $this->courseService->findCourse($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Curso recuperado com sucesso.',
                'data' => $course
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/courses/{id}",
     *     summary="Atualizar curso",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="course_code", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="duration", type="string"),
     *             @OA\Property(property="responsible", type="string"),
     *             @OA\Property(property="university_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Curso atualizado")
     * )
     */
    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        try {
            $course = $this->courseService->findCourse($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado.'
                ], 404);
            }

            $updatedCourse = $this->courseService->updateCourse($course, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Curso atualizado com sucesso.',
                'data' => $updatedCourse
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/courses/{id}",
     *     summary="Deletar curso",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Curso deletado")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $course = $this->courseService->findCourse($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado.'
                ], 404);
            }

            $this->courseService->deleteCourse($course);

            return response()->json([
                'success' => true,
                'message' => 'Curso deletado com sucesso.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/courses/stats",
     *     summary="Estatísticas dos cursos",
     *     tags={"Courses"},
     *     @OA\Response(response=200, description="Estatísticas dos cursos")
     * )
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->courseService->getStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas recuperadas com sucesso.',
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/courses/search",
     *     summary="Buscar cursos",
     *     tags={"Courses"},
     *     @OA\Parameter(name="term", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Resultados da busca")
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'term' => 'required|string|min:1'
            ]);

            $perPage = $request->input('per_page', 15);
            $courses = $this->courseService->searchCourses($request->input('term'), $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $courses->items(),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar cursos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/courses/{id}/restore",
     *     summary="Restaurar curso deletado",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Curso restaurado")
     * )
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $course = $this->courseService->restoreCourse($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado ou não estava deletado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Curso restaurado com sucesso.',
                'data' => $course
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/courses/{id}/force",
     *     summary="Deletar curso permanentemente",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Curso deletado permanentemente")
     * )
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseService->forceDeleteCourse($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Curso deletado permanentemente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar curso permanentemente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/courses/{id}/duplicate",
     *     summary="Duplicar curso",
     *     tags={"Courses"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Curso duplicado")
     * )
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            $course = $this->courseService->findCourse($id);

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso não encontrado.'
                ], 404);
            }

            $duplicatedCourse = $this->courseService->duplicateCourse($course);

            return response()->json([
                'success' => true,
                'message' => 'Curso duplicado com sucesso.',
                'data' => $duplicatedCourse->fresh(['university', 'creator', 'updater'])
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar curso.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/courses/university/{universityId}",
     *     summary="Obter cursos por universidade",
     *     tags={"Courses"},
     *     @OA\Parameter(name="universityId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cursos da universidade")
     * )
     */
    public function getByUniversity(Request $request, int $universityId): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $courses = $this->courseService->getCoursesByUniversity($universityId, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Cursos recuperados com sucesso.',
                'data' => $courses->items(),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar cursos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/courses/all/active",
     *     summary="Obter todos os cursos ativos",
     *     tags={"Courses"},
     *     @OA\Response(response=200, description="Todos os cursos ativos")
     * )
     */
    public function getAllActive(): JsonResponse
    {
        try {
            $courses = $this->courseService->getAllActiveCourses();

            return response()->json([
                'success' => true,
                'message' => 'Cursos ativos recuperados com sucesso.',
                'data' => $courses
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar cursos ativos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}