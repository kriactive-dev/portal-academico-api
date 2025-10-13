<?php

namespace App\Models\ChatBot;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class QuestionBot extends Model
{
    //
    use HasFactory;
    use BlameAble, LogsActivity, SoftDeletes;
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'text',
        'type', // 'text' ou 'button'
        'is_start', // boolean: indica se é a pergunta inicial
        'active',   // boolean: pergunta está ativa?
    ];

    public function options()
    {
        return $this->hasMany(OptionBot::class);
    }
}
