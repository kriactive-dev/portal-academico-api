<?php

namespace App\Services\RolePermission;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RolePermissionService
{
    /**
     * ===== GERENCIAMENTO DE ROLES =====
     */

    /**
     * Listar todas as roles com paginação
     */
    public function indexRoles(array $filters = []): LengthAwarePaginator
    {
        $query = Role::with(['permissions'])
            ->withCount(['permissions']);

        // Aplicar filtros se fornecidos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['guard_name']) && !empty($filters['guard_name'])) {
            $query->where('guard_name', $filters['guard_name']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        $roles = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Adicionar contagem de usuários manualmente
        foreach ($roles->items() as $role) {
            $role->users_count = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();
        }
        
        return $roles;
    }

    /**
     * Criar uma nova role
     */
    public function storeRole(array $data): Role
    {
        DB::beginTransaction();
        
        try {
            $roleData = [
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ];

            $role = Role::create($roleData);

            // Atribuir permissões se fornecidas
            if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
                $permissions = Permission::whereIn('id', $data['permission_ids'])->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();
            
            return $role->load(['permissions']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mostrar uma role específica
     */
    public function showRole(int $id): Role
    {
        $role = Role::with(['permissions'])
            ->withCount(['permissions'])
            ->find($id);

        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role não encontrada.']
            ]);
        }

        // Adicionar contagem de usuários manualmente
        $role->users_count = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->count();

        return $role;
    }

    /**
     * Atualizar uma role
     */
    public function updateRole(int $id, array $data): Role
    {
        DB::beginTransaction();
        
        try {
            $role = Role::find($id);
            
            if (!$role) {
                throw ValidationException::withMessages([
                    'role' => ['Role não encontrada.']
                ]);
            }

            // Atualizar dados básicos da role
            $roleData = array_filter([
                'name' => $data['name'] ?? null,
                'guard_name' => $data['guard_name'] ?? null,
            ], function ($value) {
                return $value !== null;
            });

            if (!empty($roleData)) {
                $role->update($roleData);
            }

            // Atualizar permissões se fornecidas
            if (isset($data['permission_ids']) && is_array($data['permission_ids'])) {
                $permissions = Permission::whereIn('id', $data['permission_ids'])->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();
            
            return $role->fresh(['permissions']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar uma role
     */
    public function destroyRole(int $id): bool
    {
        $role = Role::find($id);
        
        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role não encontrada.']
            ]);
        }

        // Verificar se tem usuários associados
        if ($role->users()->count() > 0) {
            throw ValidationException::withMessages([
                'role' => ['Não é possível excluir uma role que possui usuários. Remova os usuários primeiro.']
            ]);
        }

        return $role->delete();
    }

    /**
     * ===== GERENCIAMENTO DE PERMISSIONS =====
     */

    /**
     * Listar todas as permissions com paginação
     */
    public function indexPermissions(array $filters = []): LengthAwarePaginator
    {
        $query = Permission::with(['roles'])
            ->withCount(['roles']);

        // Aplicar filtros se fornecidos
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['guard_name']) && !empty($filters['guard_name'])) {
            $query->where('guard_name', $filters['guard_name']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Criar uma nova permission
     */
    public function storePermission(array $data): Permission
    {
        DB::beginTransaction();
        
        try {
            $permissionData = [
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ];

            $permission = Permission::create($permissionData);

            DB::commit();
            
            return $permission->load(['roles']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mostrar uma permission específica
     */
    public function showPermission(int $id): Permission
    {
        $permission = Permission::with(['roles'])
            ->withCount(['roles'])
            ->find($id);

        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission não encontrada.']
            ]);
        }

        return $permission;
    }

    /**
     * Atualizar uma permission
     */
    public function updatePermission(int $id, array $data): Permission
    {
        DB::beginTransaction();
        
        try {
            $permission = Permission::find($id);
            
            if (!$permission) {
                throw ValidationException::withMessages([
                    'permission' => ['Permission não encontrada.']
                ]);
            }

            // Atualizar dados básicos da permission
            $permissionData = array_filter([
                'name' => $data['name'] ?? null,
                'guard_name' => $data['guard_name'] ?? null,
            ], function ($value) {
                return $value !== null;
            });

            if (!empty($permissionData)) {
                $permission->update($permissionData);
            }

            DB::commit();
            
            return $permission->fresh(['roles']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deletar uma permission
     */
    public function destroyPermission(int $id): bool
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission não encontrada.']
            ]);
        }

        // Verificar se tem roles associadas
        if ($permission->roles()->count() > 0) {
            throw ValidationException::withMessages([
                'permission' => ['Não é possível excluir uma permission que está atribuída a roles. Remova das roles primeiro.']
            ]);
        }

        return $permission->delete();
    }

    /**
     * ===== ATRIBUIÇÃO DE ROLES E PERMISSIONS =====
     */

    /**
     * Atribuir role a usuário
     */
    public function assignRoleToUser(int $userId, int $roleId): User
    {
        $user = User::find($userId);
        $role = Role::find($roleId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role não encontrada.']
            ]);
        }

        $user->assignRole($role);

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Remover role de usuário
     */
    public function removeRoleFromUser(int $userId, int $roleId): User
    {
        $user = User::find($userId);
        $role = Role::find($roleId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role não encontrada.']
            ]);
        }

        $user->removeRole($role);

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Sincronizar roles de usuário
     */
    public function syncUserRoles(int $userId, array $roleIds): User
    {
        $user = User::find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->syncRoles($roles);

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Atribuir permission direta a usuário
     */
    public function assignPermissionToUser(int $userId, int $permissionId): User
    {
        $user = User::find($userId);
        $permission = Permission::find($permissionId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission não encontrada.']
            ]);
        }

        $user->givePermissionTo($permission);

        return $user->load(['roles', 'permissions']);
    }

    /**
     * Remover permission direta de usuário
     */
    public function removePermissionFromUser(int $userId, int $permissionId): User
    {
        $user = User::find($userId);
        $permission = Permission::find($permissionId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }

        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission não encontrada.']
            ]);
        }

        $user->revokePermissionTo($permission);

        return $user->load(['roles', 'permissions']);
    }

    /**
     * ===== BUSCA E FILTROS =====
     */

    /**
     * Buscar roles por critérios específicos
     */
    public function searchRoles(array $criteria): Collection
    {
        $query = Role::with(['permissions'])
            ->withCount(['permissions']);

        foreach ($criteria as $field => $value) {
            if (!empty($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query->get();
    }

    /**
     * Buscar permissions por critérios específicos
     */
    public function searchPermissions(array $criteria): Collection
    {
        $query = Permission::with(['roles'])
            ->withCount(['roles']);

        foreach ($criteria as $field => $value) {
            if (!empty($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $query->get();
    }

    /**
     * ===== ESTATÍSTICAS =====
     */

    /**
     * Obter estatísticas de roles e permissions
     */
    public function getStats(): array
    {
        return [
            'roles' => [
                'total' => Role::count(),
                'by_guard' => Role::select('guard_name', DB::raw('count(*) as count'))
                    ->groupBy('guard_name')
                    ->pluck('count', 'guard_name')
                    ->toArray(),
                // 'with_users' => Role::has('users')->count(),
                // 'without_users' => Role::doesntHave('users')->count(),
                'recent' => Role::where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'permissions' => [
                'total' => Permission::count(),
                'by_guard' => Permission::select('guard_name', DB::raw('count(*) as count'))
                    ->groupBy('guard_name')
                    ->pluck('count', 'guard_name')
                    ->toArray(),
                'assigned' => Permission::has('roles')->count(),
                'unassigned' => Permission::doesntHave('roles')->count(),
                'recent' => Permission::where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'users_with_roles' => User::has('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
        ];
    }

    /**
     * Obter todas as permissions disponíveis para uma role
     */
    public function getAvailablePermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Obter todas as roles disponíveis
     */
    public function getAvailableRoles(): Collection
    {
        return Role::with(['permissions'])->get();
    }

    /**
     * Verificar se usuário tem permissão específica
     */
    public function userHasPermission(int $userId, string $permission): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Verificar se usuário tem role específica
     */
    public function userHasRole(int $userId, string $role): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->hasRole($role);
    }

    /**
     * Obter todas as permissões de um usuário (diretas + via roles)
     */
    public function getUserPermissions(int $userId): Collection
    {
        $user = User::find($userId);
        
        if (!$user) {
            return collect([]);
        }

        return $user->getAllPermissions();
    }

    /**
     * Duplicar role com suas permissões
     */
    public function duplicateRole(int $id): Role
    {
        $originalRole = $this->showRole($id);
        
        $duplicateData = [
            'name' => $originalRole->name . ' (Cópia)',
            'guard_name' => $originalRole->guard_name,
        ];
        
        $newRole = Role::create($duplicateData);
        
        // Copiar permissões
        $permissions = $originalRole->permissions;
        $newRole->syncPermissions($permissions);
        
        return $newRole->load(['permissions']);
    }
}
