<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Models\UserProfile;
use App\Services\Auth\AuthService;
use App\Services\User\UserProfileEnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    protected $authService;
    protected UserProfileEnrichmentService $enrichmentService;

    public function __construct(AuthService $authService, UserProfileEnrichmentService $enrichmentService)
    {
        $this->authService = $authService;
        $this->enrichmentService = $enrichmentService;
    }

    /**
     * Registrar novo usuário
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->only('email', 'password'));

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
                'data' => $result
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.',
                'errors' => $e->errors()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso.'
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
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->me();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dados do usuário obtidos com sucesso.',
                'data' => $user
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
     * Atualizar perfil do usuário
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->updateProfile($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso.',
                'data' => $user
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
     * Alterar senha do usuário
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso. Faça login novamente.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function enrichProfile(Request $request): JsonResponse
    {
        try {
            if($request->has('user_id')){
                $userProfile = UserProfile::with('user')->find($request->user_id);
                if(!$userProfile){
                    return response()->json([
                        'success' => false,
                        'message' => 'Perfil não encontrado.',
                    ], 404);
                }
                $this->enrichmentService->enrichProfile($userProfile);
            }else{
                $userProfile = UserProfile::with('user')->find(Auth::user()->id);
                if(!$userProfile){
                    return response()->json([
                        'success' => false,
                        'message' => 'Perfil não encontrado.',
                    ], 404);
                }
                $this->enrichmentService->enrichProfile($userProfile);
            }
            

            return response()->json([
                'success' => true,
                'message' => 'Perfil enriquecido com sucesso.',
                'data' => $userProfile
            ], 200);

        }catch (\Exception $e) {
    
        return response()->json([
            'success' => false,
            'message' => 'Erro interno do servidor.',
            'error' => $e->getMessage()
        ], 500);
    }
    }



}
