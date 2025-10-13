<?php

namespace App\Models\ChatBot;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OptionBot extends Model
{
    //
    use HasFactory;
    use BlameAble, LogsActivity, SoftDeletes;
    
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $fillable = [
        'question_bot_id',
        'label',            // texto do botão
        'value',            // valor retornado ao clicar
        'next_question_bot_id', // id da próxima pergunta
        'created_by_user_id',
        'updated_by_user_id',
    ];

    public function question()
    {
        return $this->belongsTo(QuestionBot::class);
    }

    public function nextQuestion()
    {
        return $this->belongsTo(QuestionBot::class, 'next_question_bot_id');
    }
}
