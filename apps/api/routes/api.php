<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FeeController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RequestEntryController;
use App\Http\Controllers\Api\V1\SchoolClassController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TrainerController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);

    Route::get('courses/all/active', [CourseController::class, 'allActive']);
    Route::patch('courses/{id}/restore', [CourseController::class, 'restore'])->whereNumber('id');
    Route::delete('courses/{id}/force', [CourseController::class, 'forceDelete'])->whereNumber('id');
    Route::post('courses/{course}/duplicate', [CourseController::class, 'duplicate']);
    Route::apiResource('courses', CourseController::class);

    Route::patch('students/{id}/restore', [StudentController::class, 'restore'])->whereNumber('id');
    Route::delete('students/{id}/force', [StudentController::class, 'forceDelete'])->whereNumber('id');
    Route::patch('students/{student}/toggle-status', [StudentController::class, 'toggleStatus']);
    Route::apiResource('students', StudentController::class);

    Route::patch('trainers/{id}/restore', [TrainerController::class, 'restore'])->whereNumber('id');
    Route::delete('trainers/{id}/force', [TrainerController::class, 'forceDelete'])->whereNumber('id');
    Route::patch('trainers/{trainer}/toggle-status', [TrainerController::class, 'toggleStatus']);
    Route::apiResource('trainers', TrainerController::class);

    Route::patch('school-classes/{id}/restore', [SchoolClassController::class, 'restore'])->whereNumber('id');
    Route::delete('school-classes/{id}/force', [SchoolClassController::class, 'forceDelete'])->whereNumber('id');
    Route::apiResource('school-classes', SchoolClassController::class);

    Route::patch('fees/{id}/restore', [FeeController::class, 'restore'])->whereNumber('id');
    Route::delete('fees/{id}/force', [FeeController::class, 'forceDelete'])->whereNumber('id');
    Route::apiResource('fees', FeeController::class);

    Route::get('payments/student/{studentId}/records', [PaymentController::class, 'getByStudent'])->whereNumber('studentId');
    Route::get('payments/student/{studentId}/summary', [PaymentController::class, 'getStudentSummary'])->whereNumber('studentId');
    Route::patch('payments/{id}/restore', [PaymentController::class, 'restore'])->whereNumber('id');
    Route::delete('payments/{id}/force', [PaymentController::class, 'forceDelete'])->whereNumber('id');
    Route::apiResource('payments', PaymentController::class);

    Route::post('requests/{requestEntry}/approve', [RequestEntryController::class, 'approve']);
    Route::post('requests/{requestEntry}/deny', [RequestEntryController::class, 'deny']);
    Route::patch('requests/{id}/restore', [RequestEntryController::class, 'restore'])->whereNumber('id');
    Route::delete('requests/{id}/force', [RequestEntryController::class, 'forceDelete'])->whereNumber('id');
    Route::get('requests', [RequestEntryController::class, 'index']);
    Route::post('requests', [RequestEntryController::class, 'store']);
    Route::get('requests/{requestEntry}', [RequestEntryController::class, 'show']);
    Route::put('requests/{requestEntry}', [RequestEntryController::class, 'update']);
    Route::patch('requests/{requestEntry}', [RequestEntryController::class, 'update']);
    Route::delete('requests/{requestEntry}', [RequestEntryController::class, 'destroy']);

    Route::patch('users/{id}/restore', [UserController::class, 'restore'])->whereNumber('id');
    Route::delete('users/{id}/force', [UserController::class, 'forceDelete'])->whereNumber('id');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::apiResource('users', UserController::class);

    Route::post('import/students', [ImportController::class, 'importStudents']);
    Route::post('import/trainers', [ImportController::class, 'importTrainers']);
    Route::post('import/school-classes', [ImportController::class, 'importSchoolClasses']);
    Route::post('import/courses', [ImportController::class, 'importCourses']);
    Route::post('import/fees', [ImportController::class, 'importFees']);
    Route::post('import/payments', [ImportController::class, 'importPayments']);
});
