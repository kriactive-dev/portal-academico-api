<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ImportStudentsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function importStudents(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        ImportStudentsJob::dispatch($path, $request->user()->id);

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
