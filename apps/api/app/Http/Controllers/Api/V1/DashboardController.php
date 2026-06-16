<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\RequestEntry;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Trainer;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'totalStudents' => Student::count(),
                'activeStudents' => Student::where('status', 'active')->count(),
                'totalCourses' => Course::count(),
                'activeCourses' => Course::where('is_active', true)->count(),
                'totalClasses' => SchoolClass::count(),
                'inProgressClasses' => SchoolClass::where('status', 'in_progress')->count(),
                'totalTrainers' => Trainer::count(),
                'activeTrainers' => Trainer::where('status', 'active')->count(),
                'totalFees' => Fee::count(),
                'totalPayments' => Payment::count(),
                'pendingRequests' => RequestEntry::where('status', 'pending')->count(),
                'totalPendingPayments' => Payment::where('status', 'pending')->count(),
                'overduePayments' => Payment::where('status', 'overdue')->count(),
                'totalRevenue' => (float) Payment::where('status', 'paid')->sum('amount'),
                'activeFees' => Fee::where('is_active', true)->count(),
            ],
        ]);
    }
}
