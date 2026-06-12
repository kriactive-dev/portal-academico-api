<?php

namespace Database\Seeders;

use App\Models\Library\BookCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Ciências Exatas e Tecnológicas
            'Matemática e Estatística',
            'Física e Astronomia',
            'Química e Ciências Moleculares',
            'Ciências da Computação',
            'Engenharia Civil',
            'Engenharia Mecânica',
            'Engenharia Elétrica e Eletrônica',
            'Engenharia de Telecomunicações',
            'Arquitetura e Urbanismo',
            'Tecnologia da Informação',
            
            // Ciências Humanas e Sociais
            'História e Arqueologia',
            'Geografia e Cartografia',
            'Filosofia e Ética',
            'Sociologia',
            'Antropologia',
            'Ciência Política',
            'Relações Internacionais',
            'Comunicação Social',
            'Jornalismo',
            'Psicologia',
            
            // Ciências da Saúde
            'Medicina',
            'Enfermagem',
            'Odontologia',
            'Farmácia',
            'Nutrição',
            'Fisioterapia',
            'Medicina Veterinária',
            'Saúde Pública',
            'Biomedicina',
            
            // Ciências Jurídicas e Econômicas
            'Direito Civil',
            'Direito Penal',
            'Direito Constitucional',
            'Direito Administrativo',
            'Direito Internacional',
            'Economia',
            'Administração',
            'Contabilidade',
            'Finanças',
            'Marketing',
            
            // Ciências Agrárias e Ambientais
            'Agronomia',
            'Veterinária',
            'Engenharia Florestal',
            'Ciências Ambientais',
            'Ecologia',
            'Zootecnia',
            'Engenharia de Pesca',
            
            // Linguagens e Literatura
            'Literatura Portuguesa',
            'Literatura Africana',
            'Literatura Mundial',
            'Linguística',
            'Língua Portuguesa',
            'Línguas Estrangeiras',
            'Tradução e Interpretação',
            
            // Ciências da Educação
            'Pedagogia',
            'Didática',
            'Metodologia de Ensino',
            'Psicologia Educacional',
            'Educação Especial',
            'Gestão Educacional',
            'Currículo e Avaliação',
            
            // Artes e Cultura
            'História da Arte',
            'Artes Visuais',
            'Música',
            'Teatro e Dramaturgia',
            'Cinema e Audiovisual',
            'Cultura Moçambicana',
            'Patrimônio Cultural',
            
            // Referência e Pesquisa
            'Dicionários e Enciclopédias',
            'Metodologia de Pesquisa',
            'Estatística Aplicada',
            'Normas ABNT e Citações',
            'Teses e Dissertações',
            'Periódicos Acadêmicos',
            'Bibliografia Especializada',
            
            // Ciências Aplicadas
            'Turismo e Hotelaria',
            'Gestão de Recursos Humanos',
            'Logística e Supply Chain',
            'Empreendedorismo',
            'Inovação e Tecnologia',
            'Sustentabilidade',
            
            // Multidisciplinar
            'Estudos Africanos',
            'Estudos de Gênero',
            'Desenvolvimento Rural',
            'Cooperação Internacional',
            'Políticas Públicas',
            'Direitos Humanos',
        ];

        foreach ($categories as $categoryName) {
            BookCategory::updateOrCreate(
                ['name' => $categoryName],
                [
                    'name' => $categoryName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Categorias de livros criadas com sucesso!');
        $this->command->info('Total de categorias: ' . count($categories));
    }
}
