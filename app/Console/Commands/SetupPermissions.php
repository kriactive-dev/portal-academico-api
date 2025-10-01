<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar tabelas e dados iniciais de roles e permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Configurando sistema de roles e permissions...');

        // Verificar se as tabelas existem
        $this->checkTables();

        // Criar permissões básicas
        $this->createBasicPermissions();

        // Criar roles básicas
        $this->createBasicRoles();

        $this->info('✅ Sistema de roles e permissions configurado com sucesso!');
    }

    private function checkTables()
    {
        $this->info('📋 Verificando tabelas...');

        $tables = ['permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->line("  ✅ Tabela '$table' existe");
            } else {
                $this->error("  ❌ Tabela '$table' não encontrada");
                $this->error('Execute: php artisan migrate');
                return;
            }
        }
    }

    private function createBasicPermissions()
    {
        $this->info('🔐 Criando permissões básicas...');

        $permissions = [
            // Sistema
            'system.admin',
            'roles.manage',
            'permissions.manage',

            // Usuários
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Livros
            'books.view',
            'books.create', 
            'books.edit',
            'books.delete',

            // Bibliotecas
            'libraries.view',
            'libraries.create',
            'libraries.edit',
            'libraries.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
            $this->line("  ✅ Permission: $permission");
        }
    }

    private function createBasicRoles()
    {
        $this->info('👑 Criando roles básicas...');

        // Role Admin
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);

        $admin->syncPermissions(Permission::where('guard_name', 'api')->get());
        $this->line('  ✅ Role: admin (com todas as permissões)');

        // Role Editor
        $editor = Role::firstOrCreate([
            'name' => 'editor',
            'guard_name' => 'api'
        ]);

        $editorPermissions = [
            'users.view', 'users.create', 'users.edit',
            'books.view', 'books.create', 'books.edit',
            'libraries.view', 'libraries.create', 'libraries.edit'
        ];

        $editor->syncPermissions(Permission::whereIn('name', $editorPermissions)->get());
        $this->line('  ✅ Role: editor (permissões de edição)');

        // Role Reader
        $reader = Role::firstOrCreate([
            'name' => 'reader',
            'guard_name' => 'api'
        ]);

        $readerPermissions = [
            'users.view',
            'books.view',
            'libraries.view'
        ];

        $reader->syncPermissions(Permission::whereIn('name', $readerPermissions)->get());
        $this->line('  ✅ Role: reader (permissões de leitura)');
    }
}
