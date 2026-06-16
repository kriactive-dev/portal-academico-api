<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'estudante_id', 'curso_id', 'mes_referencia', 'valor',
        'estado', 'metodo', 'data_pagamento', 'data_vencimento',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_pagamento' => 'date',
            'data_vencimento' => 'date',
        ];
    }

    public function estudante()
    {
        return $this->belongsTo(Estudante::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
