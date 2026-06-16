<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\StudentSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStudentRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries   = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        protected User $user,
        protected string $type = 'all' // 'all' | 'academic' | 'financial'
    ) {}

    public function handle(StudentSyncService $service): void
    {
        if (in_array($this->type, ['all', 'academic'])) {
            $service->syncAcademic($this->user);
        }

        if (in_array($this->type, ['all', 'financial'])) {
            $service->syncFinancial($this->user);
        }
    }
}
