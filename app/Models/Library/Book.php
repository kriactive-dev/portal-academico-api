<?php

namespace App\Models\Library;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Book extends Model
{
    use BlameAble, LogsActivity, SoftDeletes;
    
    protected $guarded = [];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com Library
     */
    public function library()
    {
        return $this->belongsTo(\App\Models\Library\Library::class);
    }

    /**
     * Relacionamento com User (criador)
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    /**
     * Relacionamento com User (atualizador)
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }

    /**
     * Scope para buscar por título
     */
    public function scopeByTitle($query, $title)
    {
        return $query->where('title', 'like', "%{$title}%");
    }

    /**
     * Scope para buscar por autor
     */
    public function scopeByAuthor($query, $author)
    {
        return $query->where('author', 'like', "%{$author}%");
    }

    /**
     * Scope para filtrar por biblioteca
     */
    public function scopeByLibrary($query, $libraryId)
    {
        return $query->where('library_id', $libraryId);
    }

    /**
     * Accessor para URL completa da imagem
     */
    public function getImageUrlAttribute()
    {
        if ($this->book_img_path) {
            return asset('storage/' . $this->book_img_path);
        }
        return null;
    }

    /**
     * Accessor para URL completa da capa
     */
    public function getCoverUrlAttribute()
    {
        if ($this->book_cover_path) {
            return asset('storage/' . $this->book_cover_path);
        }
        return null;
    }

    /**
     * Accessor para URL completa do arquivo
     */
    public function getFileUrlAttribute()
    {
        if ($this->book_file_path) {
            return asset('storage/' . $this->book_file_path);
        }
        return null;
    }
}
