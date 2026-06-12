<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Trait\BlameAble;
use App\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentAcademicRecord extends Model
{
    use HasFactory, BlameAble, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'subject_code',
        'subject_name',
        'academic_year',
        'semester',
        'credits',
        'grade',
        'teacher_name',
        'description',
        'date',
        'user_id',
        'student_id',
        'student_code',
        'created_by_user_id',
        'updated_by_user_id',
        'deleted_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns this record
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the student (user) that this record belongs to
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the creator of this record
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the last updater of this record
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get the user who deleted this record
     */
    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    /**
     * Scope a query to search records by subject, student or teacher.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('subject_code', 'like', '%' . $term . '%')
              ->orWhere('subject_name', 'like', '%' . $term . '%')
              ->orWhere('student_code', 'like', '%' . $term . '%')
              ->orWhere('teacher_name', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%')
              ->orWhere('academic_year', 'like', '%' . $term . '%')
              ->orWhere('semester', 'like', '%' . $term . '%')
              ->orWhereHas('student', function ($subQ) use ($term) {
                  $subQ->where('name', 'like', '%' . $term . '%');
              });
        });
    }

    /**
     * Scope to filter by academic year
     */
    public function scopeAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope to filter by semester
     */
    public function scopeSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope to filter by grade
     */
    public function scopeGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * Scope to filter by subject
     */
    public function scopeSubject($query, $subjectCode)
    {
        return $query->where('subject_code', $subjectCode);
    }
}
