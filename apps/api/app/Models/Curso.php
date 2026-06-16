<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'descricao', 'duracao_meses', 'mensalidade', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'mensalidade' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function turmas()
    {
        return $this->hasMany(Turma::class);
    }

    public function taxas()
    {
        return $this->hasMany(Taxa::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }
}
