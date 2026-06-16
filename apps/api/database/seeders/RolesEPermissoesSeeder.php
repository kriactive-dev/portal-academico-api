<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesEPermissoesSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = ['dashboard', 'estudantes', 'cursos', 'turmas', 'formadores', 'taxas', 'pagamentos', 'usuarios', 'pedidos'];
        $acoes = ['ver', 'criar', 'editar', 'eliminar', 'aprovar'];

        foreach ($modulos as $modulo) {
            foreach ($acoes as $acao) {
                Permission::firstOrCreate(['name' => "{$modulo}.{$acao}", 'guard_name' => 'web']);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $secretaria = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $secretaria->syncPermissions([
            'estudantes.ver', 'estudantes.criar', 'estudantes.editar',
            'cursos.ver',
            'turmas.ver', 'turmas.criar', 'turmas.editar',
            'formadores.ver', 'formadores.criar', 'formadores.editar',
            'pedidos.ver', 'pedidos.criar', 'pedidos.editar',
            'dashboard.ver',
        ]);

        $financeiro = Role::firstOrCreate(['name' => 'financeiro', 'guard_name' => 'web']);
        $financeiro->syncPermissions([
            'taxas.ver', 'taxas.criar', 'taxas.editar',
            'pagamentos.ver', 'pagamentos.criar', 'pagamentos.editar',
            'estudantes.ver',
            'cursos.ver',
            'dashboard.ver',
        ]);

        $coordenador = Role::firstOrCreate(['name' => 'coordenador_academico', 'guard_name' => 'web']);
        $coordenador->syncPermissions([
            'cursos.ver', 'cursos.criar', 'cursos.editar', 'cursos.eliminar',
            'turmas.ver', 'turmas.criar', 'turmas.editar', 'turmas.eliminar',
            'formadores.ver', 'formadores.criar', 'formadores.editar',
            'estudantes.ver', 'estudantes.criar', 'estudantes.editar',
            'pedidos.ver', 'pedidos.aprovar',
            'dashboard.ver',
        ]);
    }
}
