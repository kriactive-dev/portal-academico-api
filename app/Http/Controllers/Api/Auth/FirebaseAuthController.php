<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\FirebaseAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class FirebaseAuthController extends Controller
{
    protected FirebaseAuthService $firebaseAuthService;

    public function __construct(FirebaseAuthService $firebaseAuthService)
    {
        $this->firebaseAuthService = $firebaseAuthService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/firebase/login",
     *     summary="Login/Registro via Firebase Token",
     *     tags={"Firebase Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firebase_token"},
     *             @OA\Property(property="firebase_token", type="string", description="Token ID do Firebase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login/Registro realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="is_new_user", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Token inválido"),
     *     @OA\Response(response=422, description="Dados de validação inválidos")
     * )
     */
    public function loginWithFirebase(Request $request): JsonResponse
    {
        try {
            // Validação do request
            $validator = Validator::make($request->all(), [
                'firebase_token' => 'required|string'
            ], [
                'firebase_token.required' => 'O token Firebase é obrigatório.',
                'firebase_token.string' => 'O token Firebase deve ser uma string válida.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de validação inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $firebaseToken = $request->input('firebase_token');

            // Autentica ou cria usuário
            $result = $this->firebaseAuthService->authenticateOrCreateUser($firebaseToken);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'is_new_user' => $result['is_new_user']
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na autenticação Firebase.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/firebase/verify-token",
     *     summary="Verificar Token Firebase",
     *     tags={"Firebase Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firebase_token"},
     *             @OA\Property(property="firebase_token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token válido"),
     *     @OA\Response(response=400, description="Token inválido")
     * )
     */
    public function verifyToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'firebase_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Firebase é obrigatório.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $firebaseToken = $request->input('firebase_token');
            $userData = $this->firebaseAuthService->verifyIdToken($firebaseToken);

            return response()->json([
                'success' => true,
                'message' => 'Token Firebase válido.',
                'data' => $userData
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token Firebase inválido.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/firebase/logout",
     *     summary="Logout Firebase",
     *     tags={"Firebase Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout realizado com sucesso")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($user && $user->firebase_uid) {
                // Revoga tokens Firebase
                $this->firebaseAuthService->revokeFirebaseTokens($user->firebase_uid);
            }

            // Revoga token Sanctum atual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/firebase/revoke-all-tokens",
     *     summary="Revogar todos os tokens do usuário",
     *     tags={"Firebase Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tokens revogados com sucesso")
     * )
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->firebase_uid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não possui Firebase UID.'
                ], 400);
            }

            // Revoga tokens Firebase
            $firebaseRevoked = $this->firebaseAuthService->revokeFirebaseTokens($user->firebase_uid);
            
            // Revoga todos os tokens Sanctum do usuário
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todos os tokens foram revogados com sucesso.',
                'data' => [
                    'firebase_tokens_revoked' => $firebaseRevoked,
                    'sanctum_tokens_revoked' => true
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar tokens.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/firebase/user-info",
     *     summary="Obter informações do usuário Firebase",
     *     tags={"Firebase Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Informações do usuário")
     * )
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->firebase_uid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não possui Firebase UID.'
                ], 400);
            }

            $firebaseUserInfo = $this->firebaseAuthService->getFirebaseUser($user->firebase_uid);

            if (!$firebaseUserInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado no Firebase.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Informações do usuário recuperadas com sucesso.',
                'data' => [
                    'local_user' => $user->load('profile'),
                    'firebase_user' => $firebaseUserInfo
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações do usuário.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/firebase/sync-user",
     *     summary="Sincronizar dados do usuário com Firebase",
     *     tags={"Firebase Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Sincronização realizada")
     * )
     */
    public function syncUser(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->firebase_uid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não possui Firebase UID.'
                ], 400);
            }

            $synced = $this->firebaseAuthService->syncUserWithFirebase($user);

            return response()->json([
                'success' => true,
                'message' => $synced ? 'Usuário sincronizado com sucesso.' : 'Nenhuma alteração necessária.',
                'data' => [
                    'synced' => $synced,
                    'user' => $user->fresh()->load('profile')
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar usuário.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/firebase/check-email",
     *     summary="Verificar se email existe no Firebase",
     *     tags={"Firebase Auth"},
     *     @OA\Parameter(name="email", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Verificação realizada")
     * )
     */
    public function checkEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email inválido.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');
            $existsInFirebase = $this->firebaseAuthService->userExistsInFirebase($email);

            return response()->json([
                'success' => true,
                'message' => 'Verificação realizada com sucesso.',
                'data' => [
                    'email' => $email,
                    'exists_in_firebase' => $existsInFirebase
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar email.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
