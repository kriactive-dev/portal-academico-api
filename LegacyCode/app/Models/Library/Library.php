<?php

namespace App\Models\Library;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Library extends Model
{
    use BlameAble, LogsActivity, SoftDeletes;
    
    protected $guarded = [];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com Books
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Relacionamento com User (criador)
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }
}
