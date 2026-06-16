<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'tipo', 'valor', 'curso_id', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
