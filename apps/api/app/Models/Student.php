<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
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
        return $this->hasMany(Request::class);
    }
}
