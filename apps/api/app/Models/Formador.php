<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formador extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'email', 'contacto', 'especialidade', 'estado',
    ];

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'formador_turma');
    }
}
