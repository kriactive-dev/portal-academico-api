<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'duration_months', 'tuition', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tuition' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
