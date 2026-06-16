<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turma extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'curso_id', 'turno', 'estado', 'data_inicio', 'data_fim',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function formadores()
    {
        return $this->belongsToMany(Formador::class, 'formador_turma');
    }

    public function estudantes()
    {
        return $this->belongsToMany(Estudante::class, 'estudante_turma');
    }
}
