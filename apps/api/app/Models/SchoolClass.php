<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $course_id
 * @property string $shift
 * @property string $status
 * @property string $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Trainer> $trainers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students
 */
class SchoolClass extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolClassFactory> */
    use HasFactory;
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
