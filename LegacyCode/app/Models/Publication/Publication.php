<?php

namespace App\Models\Publication;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Publication extends Model
{
    //
    use BlameAble, LogsActivity, SoftDeletes;
    
    protected $guarded = [];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
