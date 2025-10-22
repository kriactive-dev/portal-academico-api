<?php

namespace App\Models;

use App\Trait\BlameAble;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Publication extends Model
{
    use BlameAble, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'file_path',
        'expires_at',
        'university_id',
        'university_name',
        'year'
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relacionamento com usuário que atualizou
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Relacionamento com usuário que deletou
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeExpiringIn($query, $days)
    {
        return $query->whereBetween('expires_at', [
            now(),
            now()->addDays($days)
        ]);
    }

    public function scopeByTitle($query, $title)
    {
        return $query->where('title', 'like', "%{$title}%");
    }

    public function scopeByContent($query, $content)
    {
        return $query->where('body', 'like', "%{$content}%");
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('body', 'like', "%{$search}%");
        });
    }

    /**
     * Métodos auxiliares
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    public function daysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    public function getExpirationStatus(): string
    {
        if (!$this->expires_at) {
            return 'permanent';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        $daysUntilExpiration = $this->daysUntilExpiration();

        if ($daysUntilExpiration <= 7) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function getFileUrl(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function getFileName(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return basename($this->file_path);
    }

    public function getFileSize(): ?int
    {
        if (!$this->hasFile() || !Storage::exists($this->file_path)) {
            return null;
        }

        return Storage::size($this->file_path);
    }

    public function getFileSizeFormatted(): ?string
    {
        $size = $this->getFileSize();
        
        if (!$size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function deleteFile(): bool
    {
        if (!$this->hasFile()) {
            return true;
        }

        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }

        return true;
    }

    /**
     * Mutators
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = trim($value);
    }

    public function setBodyAttribute($value)
    {
        $this->attributes['body'] = trim($value);
    }

    /**
     * Accessors
     */
    public function getTitleAttribute($value)
    {
        return $value;
    }

    public function getBodyAttribute($value)
    {
        return $value;
    }

    public function getExcerptAttribute(): string
    {
        if (!$this->body) {
            return '';
        }

        $excerpt = strip_tags($this->body);
        return strlen($excerpt) > 150 ? substr($excerpt, 0, 150) . '...' : $excerpt;
    }

    public function getWordCountAttribute(): int
    {
        if (!$this->body) {
            return 0;
        }

        return str_word_count(strip_tags($this->body));
    }

    public function getReadingTimeAttribute(): int
    {
        $wordsPerMinute = 200;
        return max(1, ceil($this->word_count / $wordsPerMinute));
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Deletar arquivo ao deletar publicação
        static::deleting(function ($publication) {
            if ($publication->isForceDeleting()) {
                $publication->deleteFile();
            }
        });

        // Log quando publicação expira
        static::updating(function ($publication) {
            if ($publication->isDirty('expires_at') && $publication->expires_at) {
                activity()
                    ->performedOn($publication)
                    ->log('Expiration date updated to: ' . $publication->expires_at->format('Y-m-d'));
            }
        });
    }
}