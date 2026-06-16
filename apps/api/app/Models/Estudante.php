<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudante extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_estudante', 'nome', 'email', 'contacto', 'data_nascimento',
        'estado', 'data_matricula', 'encarregado_nome', 'encarregado_contacto',
        'encarregado_parentesco', 'password',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_matricula' => 'date',
            'password' => 'hashed',
        ];
    }

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'estudante_turma');
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
