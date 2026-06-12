<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuth\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Exception;

class GoogleAuthController extends Controller
{
    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Redirecionar para o Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        try {
            return $this->googleAuthService->redirectToGoogle();
        } catch (Exception $e) {
            // Em caso de erro, redirecionar para uma página de erro ou login
            return redirect(config('app.frontend_url') . '/login?error=oauth_error');
        }
    }

    /**
     * Processar callback do Google
     */
    public function callback(): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->googleAuthService->handleGoogleCallback();

            if ($result['success']) {
                // Sucesso - você pode redirecionar para o frontend com o token
                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $redirectUrl = $frontendUrl . '/auth/callback?' . http_build_query([
                    'token' => $result['token'],
                    'user' => base64_encode(json_encode($result['user'])),
                    'success' => 'true',
                    'message' => $result['message']
                ]);

                return redirect($redirectUrl);
            } else {
                // Erro - redirecionar para página de erro
                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $redirectUrl = $frontendUrl . '/login?' . http_build_query([
                    'error' => 'auth_failed',
                    'message' => $result['message']
                ]);

                return redirect($redirectUrl);
            }

        } catch (Exception $e) {
            // Em caso de exceção não tratada
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/login?' . http_build_query([
                'error' => 'unexpected_error',
                'message' => 'Erro inesperado durante a autenticação'
            ]);

            return redirect($redirectUrl);
        }
    }

    /**
     * Callback alternativo que retorna JSON (para testes)
     */
    public function callbackJson(): JsonResponse
    {
        try {
            $result = $this->googleAuthService->handleGoogleCallback();
            
            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado durante a autenticação Google.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter informações do usuário Google (para debug)
     */
    public function getUserInfo(): JsonResponse
    {
        try {
            $result = $this->googleAuthService->getGoogleUser();
            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações do usuário Google.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desvincular conta Google (para usuários autenticados)
     */
    public function unlink(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $this->googleAuthService->unlinkGoogleAccount($user);

            return response()->json([
                'success' => true,
                'message' => 'Conta Google desvinculada com sucesso.',
                'user' => $user->fresh()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
