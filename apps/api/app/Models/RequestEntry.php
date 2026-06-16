<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $student_id
 * @property string $type
 * @property string $status
 * @property string $submission_date
 * @property string|null $response_date
 * @property string|null $denial_reason
 * @property array|null $details
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Student $student
 */
class RequestEntry extends Model
{
    /** @use HasFactory<\Database\Factories\RequestEntryFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'student_id', 'type', 'status', 'submission_date',
        'response_date', 'denial_reason', 'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'submission_date' => 'datetime',
            'response_date' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
