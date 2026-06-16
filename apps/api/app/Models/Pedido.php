<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'estudante_id', 'tipo', 'estado', 'data_submissao',
        'data_resposta', 'motivo_negacao', 'detalhes',
    ];

    protected function casts(): array
    {
        return [
            'detalhes' => 'array',
            'data_submissao' => 'datetime',
            'data_resposta' => 'datetime',
        ];
    }

    public function estudante()
    {
        return $this->belongsTo(Estudante::class);
    }
}
