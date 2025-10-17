<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\User\UserProfileEnrichmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\InvalidToken;
use Illuminate\Support\Str;
use Exception;

class FirebaseAuthService
{
    protected FirebaseAuth $auth;
    protected UserProfileEnrichmentService $enrichmentService;

    public function __construct(UserProfileEnrichmentService $enrichmentService)
    {
        $this->enrichmentService = $enrichmentService;
        $this->initializeFirebaseAuth();
    }

    /**
     * Inicializa a autenticação Firebase
     */
    private function initializeFirebaseAuth(): void
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withProjectId(config('firebase.project_id'));
            
            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            Log::error('Erro ao inicializar Firebase Auth: ' . $e->getMessage());
            throw new Exception('Erro na configuração do Firebase');
        }
    }

    /**
     * Verifica e decodifica o token Firebase ID
     *
     * @param string $idToken
     * @return array
     * @throws Exception
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            
            return [
                'uid' => $verifiedIdToken->claims()->get('sub'),
                'email' => $verifiedIdToken->claims()->get('email'),
                'email_verified' => $verifiedIdToken->claims()->get('email_verified', false),
                'name' => $verifiedIdToken->claims()->get('name'),
                'picture' => $verifiedIdToken->claims()->get('picture'),
                'firebase_uid' => $verifiedIdToken->claims()->get('sub'),
            ];
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'ID token has invalid signature')) {
                Log::warning('Token Firebase inválido: ' . $e->getMessage());
                throw new Exception('Token Firebase inválido');
            }
            if (str_contains($e->getMessage(), 'revoked')) {
                Log::warning('Token Firebase revogado: ' . $e->getMessage());
                throw new Exception('Token Firebase revogado');
            }
            
            Log::error('Erro ao verificar token Firebase: ' . $e->getMessage());
            throw new Exception('Erro ao verificar token');
        }
    }

    /**
     * Autentica ou cria usuário baseado no token Firebase
     *
     * @param string $idToken
     * @return array
     * @throws Exception
     */
    public function authenticateOrCreateUser(string $idToken): array
    {
        DB::beginTransaction();
        
        try {
            // Verifica o token Firebase
            $firebaseUser = $this->verifyIdToken($idToken);

            if (!$firebaseUser['email']) {
                throw new Exception('Email é obrigatório para autenticação');
            }

            // Busca usuário existente
            $user = User::where('email', $firebaseUser['email'])
                       ->orWhere('firebase_uid', $firebaseUser['firebase_uid'])
                       ->first();

            if ($user) {
                // Usuário existe - atualiza informações se necessário
                $user = $this->updateExistingUser($user, $firebaseUser);
                $isNewUser = false;
            } else {
                // Usuário não existe - cria novo
                $user = $this->createNewUser($firebaseUser);
                $isNewUser = true;
            }

            // Gera token Sanctum
            $token = $user->createToken('firebase-auth')->plainTextToken;

            DB::commit();

            return [
                'user' => $user->load('profile'),
                'token' => $token,
                'is_new_user' => $isNewUser,
                'message' => $isNewUser ? 'Usuário criado e logado com sucesso' : 'Login realizado com sucesso'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro na autenticação Firebase: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualiza usuário existente com informações do Firebase
     *
     * @param User $user
     * @param array $firebaseUser
     * @return User
     */
    private function updateExistingUser(User $user, array $firebaseUser): User
    {
        $needsUpdate = false;

        // Atualiza firebase_uid se não estiver definido
        if (!$user->firebase_uid && $firebaseUser['firebase_uid']) {
            $user->firebase_uid = $firebaseUser['firebase_uid'];
            $needsUpdate = true;
        }

        // Atualiza email_verified_at se o email foi verificado no Firebase
        if ($firebaseUser['email_verified'] && !$user->email_verified_at) {
            $user->email_verified_at = now();
            $needsUpdate = true;
        }

        // Atualiza nome se não estiver definido
        if (!$user->name && $firebaseUser['name']) {
            $user->name = $firebaseUser['name'];
            $needsUpdate = true;
        }

        if ($needsUpdate) {
            $user->save();
        }

        // Verifica se precisa enriquecer perfil (apenas para emails @ucm.ac.mz)
        $this->checkAndEnrichProfile($user);

        return $user;
    }

    /**
     * Cria novo usuário com informações do Firebase
     *
     * @param array $firebaseUser
     * @return User
     */
    private function createNewUser(array $firebaseUser): User
    {
        $userData = [
            'name' => $firebaseUser['name'] ?? $this->extractNameFromEmail($firebaseUser['email']),
            'email' => $firebaseUser['email'],
            'firebase_uid' => $firebaseUser['firebase_uid'],
            'email_verified_at' => $firebaseUser['email_verified'] ? now() : null,
            'password' => bcrypt(Str::random(32)), // Senha aleatória - não será usada
        ];

        $user = User::create($userData);

        // Cria perfil base
        UserProfile::create([
            'user_id' => $user->id,
            'avatar_url' => $firebaseUser['picture'] ?? null,
        ]);

        // Verifica se precisa enriquecer perfil (apenas para emails @ucm.ac.mz)
        $this->checkAndEnrichProfile($user);

        return $user;
    }

    /**
     * Verifica e enriquece perfil se necessário
     *
     * @param User $user
     */
    private function checkAndEnrichProfile(User $user): void
    {
        try {
            // Só enriquece para emails da UCM
            if (str_ends_with($user->email, '@ucm.ac.mz')) {
                $this->enrichmentService->enrichProfile($user->profile);
            }
        } catch (Exception $e) {
            Log::warning('Erro ao enriquecer perfil do usuário ' . $user->id . ': ' . $e->getMessage());
            // Não falha o processo de autenticação se o enriquecimento falhar
        }
    }

    /**
     * Extrai nome do email quando não fornecido
     *
     * @param string $email
     * @return string
     */
    private function extractNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0];
        
        // Remove números e underscores, capitaliza
        $name = str_replace(['_', '.'], ' ', $localPart);
        $name = preg_replace('/\d+/', '', $name);
        
        return trim(ucwords($name)) ?: 'Usuário';
    }

    /**
     * Revoga token Firebase (logout)
     *
     * @param string $firebaseUid
     * @return bool
     */
    public function revokeFirebaseTokens(string $firebaseUid): bool
    {
        try {
            $this->auth->revokeRefreshTokens($firebaseUid);
            return true;
        } catch (Exception $e) {
            Log::error('Erro ao revogar tokens Firebase: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém informações do usuário Firebase
     *
     * @param string $firebaseUid
     * @return array|null
     */
    public function getFirebaseUser(string $firebaseUid): ?array
    {
        try {
            $userRecord = $this->auth->getUser($firebaseUid);
            
            return [
                'uid' => $userRecord->uid,
                'email' => $userRecord->email,
                'email_verified' => $userRecord->emailVerified,
                'display_name' => $userRecord->displayName,
                'photo_url' => $userRecord->photoUrl,
                'disabled' => $userRecord->disabled,
                'created_at' => $userRecord->metadata->createdAt,
                'last_sign_in' => $userRecord->metadata->lastRefreshAt ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Erro ao obter usuário Firebase: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se o usuário existe no Firebase
     *
     * @param string $email
     * @return bool
     */
    public function userExistsInFirebase(string $email): bool
    {
        try {
            $this->auth->getUserByEmail($email);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Sincroniza dados do usuário com Firebase
     *
     * @param User $user
     * @return bool
     */
    public function syncUserWithFirebase(User $user): bool
    {
        try {
            if (!$user->firebase_uid) {
                return false;
            }

            $userRecord = $this->auth->getUser($user->firebase_uid);
            
            $needsUpdate = false;

            // Sincroniza email verificado
            if ($userRecord->emailVerified && !$user->email_verified_at) {
                $user->email_verified_at = now();
                $needsUpdate = true;
            }

            // Sincroniza nome se não estiver definido
            if (!$user->name && $userRecord->displayName) {
                $user->name = $userRecord->displayName;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $user->save();
            }

            return true;
        } catch (Exception $e) {
            Log::error('Erro ao sincronizar usuário com Firebase: ' . $e->getMessage());
            return false;
        }
    }
}