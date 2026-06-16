<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ImportCoursesJob;
use App\Jobs\ImportFeesJob;
use App\Jobs\ImportPaymentsJob;
use App\Jobs\ImportSchoolClassesJob;
use App\Jobs\ImportStudentsJob;
use App\Jobs\ImportTrainersJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importStudents(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportStudentsJob::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importTrainers(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportTrainersJob::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importSchoolClasses(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportSchoolClassesJob::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importCourses(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportCoursesJob::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importFees(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportFeesJob::class);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function importPayments(Request $request): JsonResponse
    {
        return $this->dispatchImport($request, ImportPaymentsJob::class);
    }

    private function dispatchImport(Request $request, string $jobClass): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        $jobClass::dispatch($path, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Import queued successfully. You will be notified when complete.',
            'data' => [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ],
        ]);
    }
}
