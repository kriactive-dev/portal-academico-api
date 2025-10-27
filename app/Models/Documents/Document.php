<?php

namespace App\Models\Documents;

use App\Models\User;
use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use BlameAble, LogsActivity, SoftDeletes;
    
    protected $fillable = [
        'title',
        'description',
        'comments',
        'file_type',
        'user_id',
        'updated_by_user_id',
        'document_status_id',
        'document_type_id',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com User (proprietário do documento)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com User (último usuário que atualizou)
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relacionamento com DocumentStatus
     */
    public function documentStatus(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }

    /**
     * Relacionamento com DocumentFiles
     */
    public function documentFiles(): HasMany
    {
        return $this->hasMany(DocumentFile::class);
    }

    /**
     * Scopes para filtros
     */
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('document_status_id', $statusId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '<=', now()->addDays($days))
                    ->where('due_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '<', now());
    }

    /**
     * Verificar se o documento está vencido
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Verificar se é um rascunho
     */
    public function isDraft(): bool
    {
        return $this->status->name === DocumentStatus::STATUS_DRAFT;
    }

    /**
     * Verificar se está pendente
     */
    public function isPending(): bool
    {
        return $this->status->name === DocumentStatus::STATUS_PENDING;
    }

    /**
     * Verificar se foi aprovado
     */
    public function isApproved(): bool
    {
        return $this->status->name === DocumentStatus::STATUS_APPROVED;
    }

    /**
     * Verificar se foi rejeitado
     */
    public function isRejected(): bool
    {
        return $this->status->name === DocumentStatus::STATUS_REJECTED;
    }

    /**
     * Verificar se está arquivado
     */
    public function isArchived(): bool
    {
        return $this->status->name === DocumentStatus::STATUS_ARCHIVED;
    }

    /**
     * Obter contagem total de arquivos
     */
    public function getTotalFilesAttribute(): int
    {
        return $this->files()->count();
    }
}
