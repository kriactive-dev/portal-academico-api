<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\CreateUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Listar todos os usuários
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'role', 'per_page']);
            $users = $this->userService->index($filters);

            return response()->json([
                'success' => true,
                'message' => 'Usuários obtidos com sucesso.',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar um novo usuário
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso.',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar um usuário específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->show($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário obtido com sucesso.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um usuário
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar (soft delete) um usuário
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário desativado com sucesso.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar um usuário soft deleted
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $user = $this->userService->restore($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário restaurado com sucesso.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado ou não está excluído.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar permanentemente um usuário
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->userService->forceDelete($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário deletado permanentemente.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/Desativar usuário
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $user = $this->userService->toggleStatus($id);

            $message = $user->trashed() ? 'Usuário desativado' : 'Usuário ativado';

            return response()->json([
                'success' => true,
                'message' => $message . ' com sucesso.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetar senha do usuário
     */
    public function resetPassword(ResetPasswordRequest $request, int $id): JsonResponse
    {
        try {
            $this->userService->resetPassword($id, $request->validated()['new_password']);

            return response()->json([
                'success' => true,
                'message' => 'Senha resetada com sucesso. O usuário precisará fazer login novamente.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar email do usuário
     */
    public function verifyEmail(int $id): JsonResponse
    {
        try {
            $user = $this->userService->verifyEmail($id);

            return response()->json([
                'success' => true,
                'message' => 'Email verificado com sucesso.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.',
                'errors' => $e->errors()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuários
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only(['name', 'email', 'role']);
            $users = $this->userService->search($criteria);

            return response()->json([
                'success' => true,
                'message' => 'Busca realizada com sucesso.',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas de usuários
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->userService->getStats();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas obtidas com sucesso.',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
