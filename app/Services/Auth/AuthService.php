<?php

namespace App\Services\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Registrar um novo usuário
     */
    public function register(array $data): array
    {
        // Criar o usuário
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        try {
            $user->assignRole('Estudante');
            Log::info('Role "Estudante" atribuída ao usuário ' . $user->id);
        } catch (Exception $e) {
            Log::warning('Erro ao atribuir role "Estudante" ao usuário ' . $user->id . ': ' . $e->getMessage());
        }

        // O perfil já é criado automaticamente pelo método boot() do User Model

        // Gerar token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['profile','roles']),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Fazer login do usuário
     */
    public function login(array $credentials): array
    {
        // Tentar fazer login
        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $user = Auth::user();

        $studentCode = $this->extractStudentCodeFromEmail($user->email);
        if ($studentCode) {
            // Atualizar o código do estudante no perfil, se aplicável
            if ($user->profile && $user->profile->student_code !== $studentCode) {
                $user->profile->update(['student_code' => $studentCode]);
            }
        }
        
        // Revogar tokens existentes
        $user->tokens()->delete();

        // Criar novo token
        $token = $user->createToken('auth_token')->plainTextToken;


        return [
            'user' => $user->load(['profile','roles']),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Fazer logout do usuário
     */
    public function logout(): bool
    {
        $user = Auth::user();
        
        if ($user) {
            // Revogar todos os tokens do usuário
            $user->tokens()->delete();
            return true;
        }

        return false;
    }

    /**
     * Obter usuário autenticado com perfil
     */
    public function me(): ?User
    {
        $user = Auth::user();
        
        if ($user) {
            return $user->load('profile');
        }

        return null;
    }

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(array $data): User
    {
        $user = Auth::user();
        
        // Atualizar dados do usuário se fornecidos
        if (isset($data['name'])) {
            $user->update(['name' => $data['name']]);
        }

        // Atualizar dados do perfil se fornecidos
        if ($user->profile) {
            $profileData = array_filter([
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'bio' => $data['bio'] ?? null,
            ]);

            if (!empty($profileData)) {
                $user->profile->update($profileData);
            }
        }

        return $user->fresh(['profile']);
    }

    /**
     * Alterar senha do usuário
     */
    public function changePassword(array $data): bool
    {
        $user = Auth::user();
        
        // Verificar senha atual
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['A senha atual está incorreta.'],
            ]);
        }

        // Atualizar senha
        $user->update([
            'password' => Hash::make($data['new_password'])
        ]);

        // Revogar todos os tokens (força novo login)
        $user->tokens()->delete();

        return true;
    }

    private function extractStudentCodeFromEmail(string $email): ?string
    {
        if (!$this->isUCMEmail($email)) {
            return null;
        }

        $parts = explode('@', $email);
        $studentCode = $parts[0] ?? null;

        // Validar se o código parece válido (apenas números e/ou letras)
        if ($studentCode && preg_match('/^[a-zA-Z0-9]+$/', $studentCode)) {
            return $studentCode;
        }

        return null;
    }

    private function isUCMEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), '@ucm.ac.mz');
    }
}
