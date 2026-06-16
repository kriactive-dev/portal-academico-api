<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestEntry extends Model
{
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
