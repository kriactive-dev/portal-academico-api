<?php

namespace App\Models\University;

use App\Models\University\University;
use App\Models\User;
use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Course extends Model
{
    use HasFactory, SoftDeletes, BlameAble, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'course_code',
        'description',
        'duration',
        'responsible',
        'university_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com University
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    /**
     * Get the last updater of this university
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Scope para busca por nome ou código
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('course_code', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope para filtrar por universidade
     */
    public function scopeByUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    /**
     * Scope para cursos ativos
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
