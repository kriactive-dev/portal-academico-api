<?php

namespace App\Services\OAuth;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleAuthService
{
    /**
     * Redirecionar para o Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['email', 'profile'])
            ->redirect();
    }

    /**
     * Processar callback do Google e autenticar/criar usuário
     */
    public function handleGoogleCallback(): array
    {
        try {
            // Obter dados do usuário do Google
            $googleUser = Socialite::driver('google')->user();
            
            // Verificar se o domínio do email é permitido (opcional)
            $this->validateEmailDomain($googleUser->getEmail());
            
            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($googleUser);
            
            // Gerar token de acesso
            $token = $user->createToken('google-auth')->plainTextToken;

            return [
                'success' => true,
                'user' => $user->load('profile'),
                'token' => $token,
                'token_type' => 'Bearer',
                'message' => $user->wasRecentlyCreated 
                    ? 'Conta criada com sucesso via Google!' 
                    : 'Login realizado com sucesso!'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na autenticação Google: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar usuário existente ou criar novo
     */
    private function findOrCreateUser(SocialiteUser $googleUser): User
    {
        DB::beginTransaction();
        
        try {
            // Verificar se já existe usuário com este Google ID
            $user = User::where('google_id', $googleUser->getId())->first();
            
            if ($user) {
                // Atualizar informações se necessário
                $this->updateUserFromGoogle($user, $googleUser);
                DB::commit();
                return $user;
            }

            // Verificar se já existe usuário com este email
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Vincular conta Google ao usuário existente
                $this->linkGoogleAccount($user, $googleUser);
                DB::commit();
                return $user;
            }

            // Criar novo usuário
            $user = $this->createUserFromGoogle($googleUser);
            DB::commit();
            return $user;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Criar novo usuário a partir dos dados do Google
     */
    private function createUserFromGoogle(SocialiteUser $googleUser): User
    {
        $userData = [
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'provider' => 'google',
            'email_verified_at' => now(),
            'password' => null, // Usuários OAuth não precisam de senha
        ];

        $user = User::create($userData);
        
        // O perfil será criado automaticamente pelo boot method do User model
        
        return $user;
    }

    /**
     * Vincular conta Google a usuário existente
     */
    private function linkGoogleAccount(User $user, SocialiteUser $googleUser): void
    {
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            'provider' => $user->provider === 'local' ? 'google' : $user->provider,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ]);
    }

    /**
     * Atualizar informações do usuário a partir do Google
     */
    private function updateUserFromGoogle(User $user, SocialiteUser $googleUser): void
    {
        $updateData = [];

        // Atualizar nome se mudou
        if ($user->name !== $googleUser->getName()) {
            $updateData['name'] = $googleUser->getName();
        }

        // Atualizar avatar se mudou
        if ($user->avatar !== $googleUser->getAvatar()) {
            $updateData['avatar'] = $googleUser->getAvatar();
        }

        // Marcar email como verificado se não estava
        if (is_null($user->email_verified_at)) {
            $updateData['email_verified_at'] = now();
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Validar domínio do email (opcional - para restringir à universidade)
     */
    private function validateEmailDomain(string $email): void
    {
        // Descomente e configure se quiser restringir a domínios específicos
        /*
        $allowedDomains = [
            'universidade.edu.br',
            'seu-dominio.edu.br',
            // Adicione os domínios da sua universidade
        ];

        $emailDomain = substr(strrchr($email, "@"), 1);
        
        if (!in_array($emailDomain, $allowedDomains)) {
            throw new Exception('Email deve ser do domínio da universidade.');
        }
        */
    }

    /**
     * Obter informações do usuário Google sem salvar
     */
    public function getGoogleUser(): array
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            return [
                'success' => true,
                'user_info' => [
                    'id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao obter dados do Google: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Desvincular conta Google
     */
    public function unlinkGoogleAccount(User $user): bool
    {
        // Só desvincula se o usuário tiver senha (para não ficar sem forma de login)
        if (is_null($user->password)) {
            throw new Exception('Não é possível desvincular conta Google sem definir uma senha primeiro.');
        }

        return $user->update([
            'google_id' => null,
            'provider' => 'local',
        ]);
    }
}