<?php

namespace App\Services;

use App\Models\StudentRecord;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class StudentSyncService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        
    }

    public function syncAcademic(User $user): void
    {
        $response = Http::timeout(90)->get('https://api.faculdade.ac.mz/academic', [
            'student_id' => $user->student_id, // ou o campo que identificar o aluno
        ]);

        if ($response->successful()) {
            StudentRecord::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'academic_records'           => $response->json(),
                    'academic_records_synced_at' => now(),
                ]
            );
        }
    }

    public function syncFinancial(User $user): void
    {
        $response = Http::timeout(90)->get('https://api.faculdade.ac.mz/financial', [
            'student_id' => $user->student_id,
        ]);

        if ($response->successful()) {
            StudentRecord::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'financial_records'           => $response->json(),
                    'financial_records_synced_at' => now(),
                ]
            );
        }
    }
}
