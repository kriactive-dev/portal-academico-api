<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Jobs\SyncStudentRecordsJob;
use App\Services\StudentSyncService;
use Illuminate\Http\Request;

class StudentRecordController extends Controller
{
    //
    public function __construct(protected StudentSyncService $service) {}

    /**
     * Retorna os dados em cache do utilizador autenticado
     */
    public function show(Request $request)
    {
        $record = $request->user()->studentRecord;

        if (!$record) {
            return response()->json([
                'message' => 'Dados ainda não sincronizados.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'academic'  => [
                'data'      => $record->academic_records,
                'synced_at' => $record->academic_records_synced_at?->format('d/m/Y H:i'),
            ],
            'financial' => [
                'data'      => $record->financial_records,
                'synced_at' => $record->financial_records_synced_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Botão "Atualizar" — dispara sync para o utilizador autenticado
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'type' => 'sometimes|in:all,academic,financial',
        ]);

        $type   = $request->input('type', 'all');
        $user   = $request->user();
        $record = $user->studentRecord;

        // Throttle — evita spam contra o endpoint da faculdade
        $field = match($type) {
            'academic'  => 'academic_records_synced_at',
            'financial' => 'financial_records_synced_at',
            default     => 'academic_records_synced_at', // verifica o mais recente
        };

        if ($record && $record->$field?->diffInMinutes(now()) < 5) {
            return response()->json([
                'message'   => 'Dados atualizados há menos de 5 minutos. Tente novamente mais tarde.',
                'synced_at' => $record->$field->format('d/m/Y H:i'),
            ], 429);
        }

        SyncStudentRecordsJob::dispatch($user, $type);

        return response()->json([
            'message' => 'Atualização iniciada. Os dados estarão disponíveis em breve.',
        ], 202);
    }
}
