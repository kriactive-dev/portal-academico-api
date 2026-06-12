<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestFirebaseUserCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:firebase-user-creation {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a criação de usuário simulando processo Firebase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testando criação de usuário para: {$email}");
        
        // Simular dados do Firebase
        $firebaseUser = [
            'name' => 'Usuário Teste Firebase',
            'email' => $email,
            'firebase_uid' => 'firebase_test_' . Str::random(10),
            'email_verified' => true,
            'picture' => 'https://lh3.googleusercontent.com/test-avatar.jpg'
        ];
        
        // Criar usuário
        $userData = [
            'name' => $firebaseUser['name'],
            'email' => $firebaseUser['email'],
            'firebase_uid' => $firebaseUser['firebase_uid'],
            'email_verified_at' => $firebaseUser['email_verified'] ? now() : null,
            'password' => bcrypt(Str::random(32)),
        ];

        $user = User::create($userData);
        $this->info("✅ Usuário criado com ID: {$user->id}");

        // Atribuir role padrão "estudante"
        try {
            $user->assignRole('estudante');
            $this->info("✅ Role 'estudante' atribuída com sucesso");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao atribuir role: " . $e->getMessage());
        }

        // Criar perfil base
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'avatar_url' => $firebaseUser['picture'],
        ]);
        $this->info("✅ Perfil criado com ID: {$profile->id}");

        // Verificar roles atribuídas
        $roles = $user->roles()->pluck('name');
        $this->info("📋 Roles do usuário: " . $roles->implode(', '));
        
        // Verificar dados finais
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $user->id],
                ['Nome', $user->name],
                ['Email', $user->email],
                ['Firebase UID', $user->firebase_uid],
                ['Email Verificado', $user->email_verified_at ? 'Sim' : 'Não'],
                ['Roles', $roles->implode(', ')],
                ['Avatar URL', $profile->avatar_url],
            ]
        );
        
        $this->info("🎉 Teste concluído com sucesso!");
        
        return 0;
    }
}
