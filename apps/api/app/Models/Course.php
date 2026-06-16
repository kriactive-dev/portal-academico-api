<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $duration_months
 * @property float $tuition
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SchoolClass> $schoolClasses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Fee> $fees
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 */
class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;
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
