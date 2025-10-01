<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Trait\BlameAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, BlameAble, LogsActivity, HasRoles, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'provider',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Criar perfil automaticamente quando um usuário for criado
        static::created(function ($user) {
            UserProfile::create([
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * Relacionamento com UserProfile
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Verificar se o usuário foi criado via OAuth
     */
    public function isOAuthUser(): bool
    {
        return !is_null($this->provider) && $this->provider !== 'local';
    }

    /**
     * Verificar se o usuário foi criado via Google
     */
    public function isGoogleUser(): bool
    {
        return $this->provider === 'google';
    }

    /**
     * Obter avatar do usuário (OAuth ou padrão)
     */
    public function getAvatarAttribute($value): ?string
    {
        return $value ?: null;
    }

    /**
     * Verificar se pode alterar a senha
     */
    public function canChangePassword(): bool
    {
        return $this->provider === 'local' || is_null($this->provider);
    }
}
