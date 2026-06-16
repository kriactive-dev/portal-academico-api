<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Request;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Trainer;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function metrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::count(),
                'active_students' => Student::where('status', 'active')->count(),
                'total_courses' => Course::count(),
                'active_courses' => Course::where('is_active', true)->count(),
                'total_classes' => SchoolClass::count(),
                'in_progress_classes' => SchoolClass::where('status', 'in_progress')->count(),
                'total_trainers' => Trainer::count(),
                'active_trainers' => Trainer::where('status', 'active')->count(),
                'pending_requests' => Request::where('status', 'pending')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'overdue_payments' => Payment::where('status', 'overdue')->count(),
                'total_revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
                'active_fees' => Fee::where('is_active', true)->count(),
            ],
        ]);
    }
}
