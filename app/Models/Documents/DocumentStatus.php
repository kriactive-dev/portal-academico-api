<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentStatus extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relacionamento com Documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Constantes para os status padrão
     */
    const STATUS_DRAFT = 'Rascunho';
    const STATUS_PENDING = 'Pendente';
    const STATUS_APPROVED = 'Aprovado';
    const STATUS_REJECTED = 'Rejeitado';
    const STATUS_ARCHIVED = 'Arquivado';

    /**
     * Obter lista de status padrão
     */
    public static function getDefaultStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_ARCHIVED,
        ];
    }
}
