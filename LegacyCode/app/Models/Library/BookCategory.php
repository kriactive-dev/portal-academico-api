<?php

namespace App\Models\Library;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BookCategory extends Model
{
    use HasFactory, BlameAble, LogsActivity, SoftDeletes;
    
    /**
     * Campos que podem ser preenchidos em massa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Campos que devem ser castados para tipos específicos
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Configurações do log de atividades
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relacionamento com livros
     * Uma categoria pode ter muitos livros
     */
    public function books()
    {
        return $this->hasMany(\App\Models\Library\Book::class, 'category_id');
    }

    /**
     * Scope para buscar categorias por nome
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            return $query->where('name', 'like', '%' . $search . '%');
        }
        
        return $query;
    }

    /**
     * Scope para ordenar alfabeticamente
     */
    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Accessor para o nome formatado
     */
    public function getFormattedNameAttribute()
    {
        return ucfirst($this->name);
    }

    /**
     * Mutator para o nome (remove espaços extras)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }
}
