<?php

namespace Database\Seeders;

use App\Models\University\University;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar a Universidade Católica de Moçambique
        

        // Dados das Faculdades da UCM com suas respetivas localizações
        $faculdades = [
            [
                'nome' => 'Faculdade de Ciências de Saúde',
                
            ],
            [
                'nome' => 'Faculdade de Economia e Gestão',
                
            ],
            [
                'nome' => 'Faculdade de Engenharia',
                
            ],
            [
                'nome' => 'Faculade de Gestão de Recursos Naturais e Mineralogia',
                
            ],
            [
                'nome' => 'Faculdade de Ciências Sociais e Políticas',
                
            ],
            [
                'nome' => 'Faculdade de Direito',
                
            ],
            [
                'nome' => 'Faculdade de Educação e Comunicação',
                
            ],
            [
                'nome' => 'Extensão de Maputo',
            ],
            [
                'nome' => 'Extensão de Nacala',
            ],
            [
                'nome' => 'Extensão de Gurué',
            ],
            [
                'nome' => 'Extensão de Xai-Xai',
            ],
            [
                'nome' => 'Faculdade de Ciências Agronomicas ',
            ],
            [
                'nome' => 'Faculdade de Gestão de Recursos Florestatis e Faunísticos',
            ],
            [
                'nome' => 'Faculdade de Gestão de Turismo e Informática ',
            ],
            
        ];

        // Log das faculdades criadas (para referência futura)
        foreach ($faculdades as $faculdade) {
            University::create([
                'name' => $faculdade['nome']
            ]);
        }
    }
}

// php artisan db:seed --class=UniversitySeeder                                 