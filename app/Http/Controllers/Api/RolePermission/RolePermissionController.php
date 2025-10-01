<?php

namespace App\Http\Controllers\Api\RolePermission;

use App\Http\Controllers\Controller;
use App\Services\RolePermission\RolePermissionService;
use App\Http\Requests\Api\RolePermission\CreateRoleRequest;
use App\Http\Requests\Api\RolePermission\UpdateRoleRequest;
use App\Http\Requests\Api\RolePermission\CreatePermissionRequest;
use App\Http\Requests\Api\RolePermission\UpdatePermissionRequest;
use App\Http\Requests\Api\RolePermission\AssignRoleRequest;
use App\Http\Requests\Api\RolePermission\AssignPermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RolePermissionController extends Controller
{
    protected $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * ===== GERENCIAMENTO DE ROLES =====
     */

    /**
     * Listar roles
     */
    public function indexRoles(Request $request): JsonResponse
    {
        try {
            $roles = $this->rolePermissionService->indexRoles($request->all());

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar roles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar role
     */
    public function storeRole(CreateRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->rolePermissionService->storeRole($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Role criada com sucesso.',
                'data' => $role
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Visualizar role
     */
    public function showRole(int $id): JsonResponse
    {
        try {
            $role = $this->rolePermissionService->showRole($id);

            return response()->json([
                'success' => true,
                'data' => $role
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role não encontrada.',
                'errors' => $e->errors()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar role
     */
    public function updateRole(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $role = $this->rolePermissionService->updateRole($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Role atualizada com sucesso.',
                'data' => $role
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar role
     */
    public function destroyRole(int $id): JsonResponse
    {
        try {
            $this->rolePermissionService->destroyRole($id);

            return response()->json([
                'success' => true,
                'message' => 'Role excluída com sucesso.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar role
     */
    public function duplicateRole(int $id): JsonResponse
    {
        try {
            $role = $this->rolePermissionService->duplicateRole($id);

            return response()->json([
                'success' => true,
                'message' => 'Role duplicada com sucesso.',
                'data' => $role
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role não encontrada.',
                'errors' => $e->errors()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ===== GERENCIAMENTO DE PERMISSIONS =====
     */

    /**
     * Listar permissions
     */
    public function indexPermissions(Request $request): JsonResponse
    {
        try {
            $permissions = $this->rolePermissionService->indexPermissions($request->all());

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar permissions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar permission
     */
    public function storePermission(CreatePermissionRequest $request): JsonResponse
    {
        try {
            $permission = $this->rolePermissionService->storePermission($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Permission criada com sucesso.',
                'data' => $permission
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Visualizar permission
     */
    public function showPermission(int $id): JsonResponse
    {
        try {
            $permission = $this->rolePermissionService->showPermission($id);

            return response()->json([
                'success' => true,
                'data' => $permission
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission não encontrada.',
                'errors' => $e->errors()
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar permission
     */
    public function updatePermission(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        try {
            $permission = $this->rolePermissionService->updatePermission($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Permission atualizada com sucesso.',
                'data' => $permission
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar permission
     */
    public function destroyPermission(int $id): JsonResponse
    {
        try {
            $this->rolePermissionService->destroyPermission($id);

            return response()->json([
                'success' => true,
                'message' => 'Permission excluída com sucesso.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ===== ATRIBUIÇÃO DE ROLES E PERMISSIONS =====
     */

    /**
     * Atribuir role a usuário
     */
    public function assignRoleToUser(AssignRoleRequest $request): JsonResponse
    {
        try {
            $user = $this->rolePermissionService->assignRoleToUser(
                $request->user_id,
                $request->role_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Role atribuída ao usuário com sucesso.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atribuir role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover role de usuário
     */
    public function removeRoleFromUser(Request $request): JsonResponse
    {
        try {
            $user = $this->rolePermissionService->removeRoleFromUser(
                $request->user_id,
                $request->role_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Role removida do usuário com sucesso.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar roles de usuário
     */
    public function syncUserRoles(AssignRoleRequest $request): JsonResponse
    {
        try {
            $user = $this->rolePermissionService->syncUserRoles(
                $request->user_id,
                $request->role_ids ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Roles sincronizadas com sucesso.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar roles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atribuir permission direta a usuário
     */
    public function assignPermissionToUser(AssignPermissionRequest $request): JsonResponse
    {
        try {
            $user = $this->rolePermissionService->assignPermissionToUser(
                $request->user_id,
                $request->permission_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Permission atribuída ao usuário com sucesso.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atribuir permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover permission direta de usuário
     */
    public function removePermissionFromUser(Request $request): JsonResponse
    {
        try {
            $user = $this->rolePermissionService->removePermissionFromUser(
                $request->user_id,
                $request->permission_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Permission removida do usuário com sucesso.',
                'data' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ===== BUSCA E CONSULTAS =====
     */

    /**
     * Buscar roles
     */
    public function searchRoles(Request $request): JsonResponse
    {
        try {
            $roles = $this->rolePermissionService->searchRoles($request->all());

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar roles.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar permissions
     */
    public function searchPermissions(Request $request): JsonResponse
    {
        try {
            $permissions = $this->rolePermissionService->searchPermissions($request->all());

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar permissions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter permissions disponíveis
     */
    public function getAvailablePermissions(): JsonResponse
    {
        try {
            $permissions = $this->rolePermissionService->getAvailablePermissions();

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar permissions disponíveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter roles disponíveis
     */
    public function getAvailableRoles(): JsonResponse
    {
        try {
            $roles = $this->rolePermissionService->getAvailableRoles();

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar roles disponíveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter permissões de usuário
     */
    public function getUserPermissions(int $userId): JsonResponse
    {
        try {
            $permissions = $this->rolePermissionService->getUserPermissions($userId);

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar permissões do usuário.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se usuário tem permissão
     */
    public function checkUserPermission(Request $request): JsonResponse
    {
        try {
            $hasPermission = $this->rolePermissionService->userHasPermission(
                $request->user_id,
                $request->permission
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'has_permission' => $hasPermission,
                    'user_id' => $request->user_id,
                    'permission' => $request->permission
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar permissão.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se usuário tem role
     */
    public function checkUserRole(Request $request): JsonResponse
    {
        try {
            $hasRole = $this->rolePermissionService->userHasRole(
                $request->user_id,
                $request->role
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'has_role' => $hasRole,
                    'user_id' => $request->user_id,
                    'role' => $request->role
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar role.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->rolePermissionService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
