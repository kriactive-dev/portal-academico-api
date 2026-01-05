<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Listar todos os usuários com paginação
     */
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['profile', 'roles']);

        // Aplicar filtros se fornecidos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            }
        }

        if (isset($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Criar um novo usuário
     */
    public function store(array $data): User
    {
        DB::beginTransaction();
        
        try {
            // Criar o usuário
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => $data['email_verified'] ?? false ? now() : null,
            ];

            $user = User::create($userData);

            // Atribuir roles se fornecidas
            if (isset($data['roles']) && is_array($data['roles'])) {
                $user->assignRole($data['roles']);
            }

            // Atualizar perfil se dados fornecidos
            if (isset($data['profile']) && is_array($data['profile'])) {
                $user->profile->update($data['profile']);
            }

            DB::commit();
            
            return $user->load(['profile', 'roles']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mostrar um usuário específico
     */
    public function show(int $id): User
    {
        $user = User::with(['profile', 'roles'])
            ->withTrashed()
            ->find($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        return $user;
    }

    /**
     * Atualizar um usuário
     */
    public function update(int $id, array $data): User
    {
        DB::beginTransaction();
        
        try {
            $user = User::withTrashed()->find($id);
            
            if (!$user) {
                throw ValidationException::withMessages([
                    'user' => ['Usuário não encontrado.']
                ]);
            }

            // Atualizar dados básicos do usuário
            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            if (!empty($userData)) {
                $user->update($userData);
            }

            // Atualizar senha se fornecida
            if (isset($data['password']) && !empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password'])
                ]);
            }

            // Verificar email se solicitado
            if (isset($data['email_verified'])) {
                $user->update([
                    'email_verified_at' => $data['email_verified'] ? now() : null
                ]);
            }

            // Atualizar roles se fornecidas
            if (isset($data['roles']) && is_array($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            // Atualizar perfil se dados fornecidos
            if (isset($data['profile']) && is_array($data['profile']) && $user->profile) {
                $user->profile->update($data['profile']);
            }

            DB::commit();
            
            return $user->fresh(['profile', 'roles']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar (soft delete) um usuário
     */
    public function destroy(int $id): bool
    {
        $user = User::find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        return $user->delete();
    }

    /**
     * Restaurar um usuário soft deleted
     */
    public function restore(int $id): User
    {
        $user = User::onlyTrashed()->find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado ou não está excluído.']
            ]);
        }

        $user->restore();
        
        return $user->load(['profile', 'roles']);
    }

    /**
     * Deletar permanentemente um usuário
     */
    public function forceDelete(int $id): bool
    {
        $user = User::withTrashed()->find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        return $user->forceDelete();
    }

    /**
     * Ativar/Desativar usuário
     */
    public function toggleStatus(int $id): User
    {
        $user = User::withTrashed()->find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        if ($user->trashed()) {
            $user->restore();
        } else {
            $user->delete();
        }

        return $user->fresh(['profile', 'roles']);
    }

    /**
     * Resetar senha do usuário
     */
    public function resetPassword(int $id, string $newPassword): bool
    {
        $user = User::find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        // Revogar todos os tokens do usuário
        $user->tokens()->delete();

        return true;
    }

    /**
     * Verificar email do usuário
     */
    public function verifyEmail(int $id): User
    {
        $user = User::find($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        $user->update([
            'email_verified_at' => now()
        ]);

        return $user->fresh(['profile', 'roles']);
    }

    /**
     * Buscar usuários por critérios específicos
     */
    public function search(array $criteria): Collection
    {
        $query = User::with(['profile', 'roles']);

        foreach ($criteria as $field => $value) {
            if (in_array($field, ['name', 'email'])) {
                $query->where($field, 'like', "%{$value}%");
            } elseif ($field === 'role') {
                $query->whereHas('roles', function ($q) use ($value) {
                    $q->where('name', $value);
                });
            }
        }

        return $query->get();
    }

    /**
     * Obter estatísticas de usuários
     */
    public function getStats(): array
    {
        return [
            'total' => User::withTrashed()->count(),
            'active' => User::count(),
            'inactive' => User::onlyTrashed()->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }
}
