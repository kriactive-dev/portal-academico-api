<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RequestEntry;

/**
 * @property int $id
 * @property string $student_number
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $birth_date
 * @property string $status
 * @property string $enrollment_date
 * @property string|null $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_relationship
 * @property string|null $password
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SchoolClass> $schoolClasses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RequestEntry> $requests
 */
class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'student_number', 'name', 'email', 'phone', 'birth_date',
        'status', 'enrollment_date', 'guardian_name', 'guardian_phone',
        'guardian_relationship', 'password',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'enrollment_date' => 'date',
        ];
    }

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function requests()
    {
        return $this->hasMany(RequestEntry::class);
    }
}
