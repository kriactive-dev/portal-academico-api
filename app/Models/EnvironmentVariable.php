<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EnvironmentVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'is_active',
        'is_encrypted',
        'category',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_encrypted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accessor para descriptografar o valor se necessário
     */
    public function getDecryptedValueAttribute()
    {
        if ($this->is_encrypted && $this->value) {
            try {
                return Crypt::decryptString($this->value);
            } catch (\Exception $e) {
                return $this->value; // Retorna o valor original se não conseguir descriptografar
            }
        }

        return $this->value;
    }

    /**
     * Mutator para criptografar o valor se necessário
     */
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Scope para buscar apenas variáveis ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para buscar por chave
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Método estático para buscar valor por chave
     */
    public static function getValue($key, $default = null)
    {
        $variable = self::active()->byKey($key)->first();
        
        if (!$variable) {
            return $default;
        }

        return $variable->getDecryptedValueAttribute();
    }

    /**
     * Método estático para definir uma variável
     */
    public static function setValue($key, $value, $description = null, $category = null, $isEncrypted = false)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'category' => $category,
                'is_encrypted' => $isEncrypted,
                'is_active' => true,
            ]
        );
    }
}