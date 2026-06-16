<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'course_id', 'shift', 'status', 'start_date', 'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function trainers()
    {
        return $this->belongsToMany(Trainer::class, 'class_trainer');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student');
    }
}
