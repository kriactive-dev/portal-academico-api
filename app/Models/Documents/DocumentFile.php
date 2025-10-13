<?php

namespace App\Models\Documents;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentFile extends Model
{
    use BlameAble, LogsActivity, SoftDeletes;
    
    protected $fillable = [
        'document_id',
        'file_path',
        'file_name',
        'original_name',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com Document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Obter URL completa do arquivo
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Obter tamanho do arquivo formatado
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($documentFile) {
            if ($documentFile->file_path && Storage::disk('public')->exists($documentFile->file_path)) {
                Storage::disk('public')->delete($documentFile->file_path);
            }
        });
    }
}
