<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'specialty', 'status',
    ];

    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_trainer');
    }
}
