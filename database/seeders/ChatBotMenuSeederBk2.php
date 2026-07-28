<?php

namespace Database\Seeders;

use App\Models\ChatBot\OptionBot;
use App\Models\ChatBot\QuestionBot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder que monta toda a árvore do chatbot (menu principal + submenus + respostas finais).
 *
 * Dados de "Cursos" (2.1, 2.2, 2.4) e "Calendário Académico" (5.1 a 5.4) foram
 * extraídos dos ficheiros oficiais:
 *   - Cursos_da_UCM.xlsx        (lista de cursos por faculdade/campus)
 *   - Calendario_Academico_2026.pdf (datas oficiais do ano académico 2026)
 *
 * As restantes secções (1, 3, 4, 6, 7, 8, 9) continuam com texto placeholder
 * (marcado com "// TODO") porque não havia documento fonte para elas — edite
 * antes de subir para produção.
 *
 * ESTRUTURA DE DADOS:
 * - Se o valor de uma opção for uma STRING => é uma "folha" (resposta final).
 * - Se o valor for um ARRAY com 'text' e 'options' => é um submenu.
 *
 * Rodar com: php artisan db:seed --class=ChatBotMenuSeeder
 */
class ChatBotMenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tree = $this->menuTree();
            $this->buildNode($tree, isStart: true);
        });

        $this->command?->info('Chatbot: árvore de menu criada com sucesso.');
    }

    /**
     * Cria recursivamente uma QuestionBot (e, se for submenu, as OptionBot filhas).
     * Retorna o id da QuestionBot criada.
     */
    private function buildNode(array $node, bool $isStart = false): int
    {
        $question = QuestionBot::create([
            'text'     => $node['text'],
            'type'     => 'button', // 'button' garante que o "Voltar" injetado em runtime funcione
            'is_start' => $isStart,
            'active'   => true,
        ]);

        $i = 1;
        foreach ($node['options'] ?? [] as $label => $child) {
            if (is_string($child)) {
                $childId = QuestionBot::create([
                    'text'     => $child,
                    'type'     => 'button',
                    'is_start' => false,
                    'active'   => true,
                ])->id;
            } else {
                $childId = $this->buildNode($child, isStart: false);
            }

            OptionBot::create([
                'question_bot_id'      => $question->id,
                'label'                => $label,
                'value'                => 'q' . $question->id . '_o' . $i,
                'next_question_bot_id' => $childId,
            ]);

            $i++;
        }

        return $question->id;
    }

    /**
     * Árvore completa do menu.
     */
    private function menuTree(): array
    {
        // ------------------------------------------------------------------
        // Textos longos (cursos por campus, licenciaturas, mestrados)
        // extraídos de Cursos_da_UCM.xlsx
        // ------------------------------------------------------------------
$licenciaturasText = <<<'EOT'
🎓 *Licenciaturas disponíveis na UCM* (a oferta exacta varia por campus):

• Engenharia Alimentar
• Engenharia Civil
• Engenharia Electrotécnica
• Engenharia Geológica
• Engenharia Mecânica
• Engenharia de Minas
• Engenharia de Processamento Mineral
• Licenciatura Administração Pública
• Licenciatura Ciências Pol. e Relaçes Internacionais
• Licenciatura Contabilidade e Auditoria
• Licenciatura Tecnologias de Informação
• Licenciatura de Desenvolvi. Comunitário e Serviço Social
• Licenciatura de Psicopedagogia
• Licenciatura em Administração Hospitalar
• Licenciatura em Administração Pública
• Licenciatura em Administração e Gestão Hospitalar
• Licenciatura em Administração e Gestão de Empresas
• Licenciatura em Agronomia
• Licenciatura em Análises Clinicas e Laboratoriais
• Licenciatura em Arquitectura
• Licenciatura em Ciências Pol. e Relações Internacionais
• Licenciatura em Contabilidade e Auditoria
• Licenciatura em Criminologia e Justiça Criminal
• Licenciatura em Desenvolvimento Comunitário e Serviço Social
• Licenciatura em Direito
• Licenciatura em Economia e Gestão
• Licenciatura em Enfermagem Superior
• Licenciatura em Farmácia
• Licenciatura em Gestão Ambiental
• Licenciatura em Gestão Portuária
• Licenciatura em Gestão de Recursos Florestais e Faunísticos
• Licenciatura em Gestão de Recursos Humanos
• Licenciatura em Gestão de Recursos Humanos e Relações Laborais
• Licenciatura em Gestão de Relaçoes Públias e Marketing Estratégico
• Licenciatura em Gestão do Meio Ambiente e Recursos Naturais
• Licenciatura em Gestão e Administração Educacional
• Licenciatura em Medicina Geral
• Licenciatura em Psicologia Clínica e Assistência Social
• Licenciatura em Tecnologias de Informação

Para saber quais destes cursos estão disponíveis no seu campus, veja a opção "Cursos por campus".
EOT;
$mestradosText = <<<'EOT'
🎓 *Mestrados disponíveis na UCM* (a oferta exacta varia por campus):

• Mestrado Contabilidade e Auditoria
• Mestrado Penal
• Mestrado em Administração Pública
• Mestrado em Administração e Gestão de Negócios (MBA)
• Mestrado em Ciência Politica: Governação e Relações Internacionais
• Mestrado em Ciências Política: Governação e Relações Internacionais
• Mestrado em Comunicação para Desenvolvimento
• Mestrado em Contabilidade e Auditoria
• Mestrado em Desenvolvimento Sustentável de Recursos Florestais e Faunísticos
• Mestrado em Direito Administrativo
• Mestrado em Direito Civil
• Mestrado em Direito Constitucional
• Mestrado em Direito Empresarial
• Mestrado em Direito Fiscal
• Mestrado em Direito Penal
• Mestrado em Direito de Petróleo e Gás
• Mestrado em Direito de Trabalho
• Mestrado em Direito e Desenvolvimento Sustentável
• Mestrado em Direitos Humanos, Justiça e Paz
• Mestrado em Economia
• Mestrado em Gestão de Projectos de Desenvolvimento
• Mestrado em Gestão de Recursos Humanos
• Mestrado em Gestão e Administração Educacional
• Mestrado em Gestão em Relações Públicas e Marketing Estratégico
• Mestrado em Planeamento e Desenvolvimento Regional
• Mestrado em Psicologia de Saúde
• Mestrado em Psicopedagogia
• Mestrado em Saúde Pública
• Mestrado em Sistemas de Informação Geográfica e Monitoria de Recursos Naturais
• Mestrado em Solos e Agricultura Sustentável
• Mestrado em Tecnologias de Informação

*Doutoramentos disponíveis:*
• Doutoramento em Ciências da Comunicação
• Doutoramento em Direito Privado
• Doutoramento em Direito Público
• Doutoramento em Inovação Educativa

Para saber quais estão disponíveis no seu campus, veja a opção "Cursos por campus".
EOT;
$campus1Text = <<<'EOT'
📍 *Faculdade de Ciências de Saúde - Beira*
Cursos disponíveis:

• Licenciatura em Medicina Geral (Duração: 6 anos/ L; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Enfermagem Superior (Duração: 4 anos/ L e PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração Hospitalar (Duração: 4 anos/ L e PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Farmácia (Duração: 4 anos/ L; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Análises Clinicas e Laboratoriais (Duração: 4 anos/L e PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Psicologia Clínica e Assistência Social (Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Saúde Pública (Duração: 2 anos PL; Requisitos: Licenciatura em Ciências de Saúde)
• Mestrado em Psicologia de Saúde (Duração: 2 anos PL; Requisitos: Licenciatura em Ciências de Saúde)
EOT;
$campus2Text = <<<'EOT'
📍 *Faculdade de Economia e Gestão - Beira*
Cursos disponíveis:

• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Arquitectura (Duração: 5 anos L; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração e Gestão de Empresas (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão Portuária (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
• Mestrado em Direito Administrativo (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito Empresarial (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito Penal (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado Contabilidade e Auditoria (Duração: 2 anos PL; Requisitos: Lic. em Contabilidade e Auditoria ou outras áreas afins)
• Mestrado em Gestão de Recursos Humanos (Duração: 2 anos PL; Requisitos: Lic. em Gestão de Recursos Humanos)
• Mestrado em Sistemas de Informação Geográfica e Monitoria de Recursos Naturais (Duração: 2 anos PL; Requisitos: Lic. em Sistema de Informação ou áreas afins)
• Mestrado em Economia (Duração: 2 anos PL; Requisitos: Lic. em Economia ou outras areas afins)
• Mestrado em Planeamento e Desenvolvimento Regional (Duração: 2 anos PL; Requisitos: Lic. em Gestão, Planeamento ou áreas afins)
EOT;
$campus3Text = <<<'EOT'
📍 *Faculdade de Engenharia - Chimoio*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Agronomia (Duração: 5 anos L; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Psicologia Clínica e Assistência Social (Duração: 4 anos L; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia Alimentar (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia Civil (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia Electrotécnica (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia Mecânica (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Direito Administrativo (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
EOT;
$campus4Text = <<<'EOT'
📍 *Faculdade de Gestão dos Recursos Naturais e Mineralogia-Tete*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/Pl; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão Ambiental (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia de Processamento Mineral (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia de Minas (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Engenharia Geológica (Duração: 5 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas fins)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Direito Empresarial (Duração: 2 anos PL; Requisitos: Lic. em Direito)
• Mestrado em Gestão de Projectos de Desenvolvimento (Duração: 2 anos PL; Requisitos: Lic. em Comunicaçã ou áreas)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
EOT;
$campus5Text = <<<'EOT'
📍 *Faculdade de Ciências Sociais e Politicas - Quelimane*
Cursos disponíveis:

• Licenciatura em Administração e Gestão de Empresas (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração e Gestão Hospitalar (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Ciências Pol. e Relações Internacionais (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura de Desenvolvi. Comunitário e Serviço Social (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Tecnologias de Informação (Duração: 2 anos PL; Requisitos: Lic. em IT ou áreas afins)
• Mestrado em Ciências Política: Governação e Relações Internacionais (Duração: 2 anos PL; Requisitos: Lic. em Ciências Politicas ou outras áreas afins)
• Mestrado em Direito Administrativo (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Gestão de Projectos de Desenvolvimento (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Contabilidade e Auditoria (Duração: 2 anos PL; Requisitos: Lic. em Cont. e Auditoria ou outras áreas afins)
• Mestrado em Saúde Pública (Duração: 2 anos PL; Requisitos: Lic. em Ciências de Saúde)
EOT;
$campus6Text = <<<'EOT'
📍 *Faculdade de Direito - Nampula*
Cursos disponíveis:

• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura Ciências Pol. e Relaçes Internacionais (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Criminologia e Justiça Criminal (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Direito Civil (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito e Desenvolvimento Sustentável (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito Fiscal (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado Penal (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Ciências Política: Governação e Relações Internacionais (Duração: 2 anos PL; Requisitos: Lic. em Ciências Politicas ou áreas afins)
• Mestrado em Direito Empresarial (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito e Desenvolvimento Sustentável (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito de Petróleo e Gás (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito Constitucional (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Direito de Trabalho (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Doutoramento em Direito Público (Duração: 3 anos PL; Requisitos: Mestrado em Direito ou outras áreas afins)
• Doutoramento em Direito Privado (Duração: 3 anos PL; Requisitos: Mestrado em Direito ou outras áreas afins)
EOT;
$campus7Text = <<<'EOT'
📍 *Faculdade de Educação e Comunicação - Nampula*
Cursos disponíveis:

• Licenciatura de Psicopedagogia (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão e Administração Educacional (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Relaçoes Públias e Marketing Estratégico (Duração: 4 anos L/Pl; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Desenvolvimento Comunitário e Serviço Social (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos e Relações Laborais (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Gestão de Recursos Humanos (Duração: 2 anos PL; Requisitos: Lic. em Gestão de Recursos Humanos)
• Mestrado em Gestão em Relações Públicas e Marketing Estratégico (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Comunicação para Desenvolvimento (Duração: 2 anos PL; Requisitos: Lic. Comunicação ou outras áreas afins)
• Mestrado em Gestão de Projectos de Desenvolvimento (Duração: 2 ano PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Doutoramento em Inovação Educativa (Duração: 3 anos PL; Requisitos: Mestrado em Ciências de Educação ou outras áreas afins)
• Doutoramento em Ciências da Comunicação (Duração: 3 anos PL; Requisitos: Mestrado em Ciências de Comunicação ou outras áreas afins)
EOT;
$campus8Text = <<<'EOT'
📍 *Extensão de Maputo*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Ciências Pol. e Relações Internacionais (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. Em Gestão ou outras áreas afins)
• Mestrado em Direito Administrativo (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Contabilidade e Auditoria (Duração: 2 anos PL; Requisitos: Lic. Contabilidade ou outras áreas afins)
• Mestrado em Saúde Pública (Duração: 2 anos PL; Requisitos: Lic. Em Ciências de Saúde)
EOT;
$campus9Text = <<<'EOT'
📍 *Extensão de Nacala*
Cursos disponíveis:

• Licenciatura em Gestão Portuária (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Gestão de Recursos Humanos (Duração: 2 anos PL; Requisitos: Lic. em Gestão de Recursos Humanos)
EOT;
$campus10Text = <<<'EOT'
📍 *Extensão de Gurué*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Psicopedagogia (Duração: 2 anos PL; Requisitos: Lic. em Psicopedagógia ou outras áreas afins)
EOT;
$campus11Text = <<<'EOT'
📍 *Extensão de Xai-Xai*
Cursos disponíveis:

• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Direitos Humanos, Justiça e Paz (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Ciência Politica: Governação e Relações Internacionais (Duração: 2 anos PL)
• Mestrado em Contabilidade e Auditoria (Duração: 2 anos PL)
EOT;
$campus12Text = <<<'EOT'
📍 *Faculdade de Ciências Agronomicas - Cuamba*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Agronomia (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Solos e Agricultura Sustentável (Duração: 2 anos PL; Requisitos: Lic.em Ciências Agrárias ou outras áreas afins)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. Em Gestão ou outras áreas afins)
EOT;
$campus13Text = <<<'EOT'
📍 *Faculdade de Gestão de Recursos Florestais e Faunísticos -Lichinga*
Cursos disponíveis:

• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Gestão de Recursos Florestais e Faunísticos (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Administração e Gestão Hospitalar (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe /equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Direito Administrativo (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
• Mestrado em Desenvolvimento Sustentável de Recursos Florestais e Faunísticos (Duração: 2 anos PL; Requisitos: Lic. em Ciências Agrárias, Biológicas Naturais ou outras áreas afins)
EOT;
$campus14Text = <<<'EOT'
📍 *Faculdade de Gestão de Turismo e Informática - Pemba*
Cursos disponíveis:

• Licenciatura em Administração Pública (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Contabilidade e Auditoria (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Direito (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Economia e Gestão (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Gestão de Recursos Humanos (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Gestão do Meio Ambiente e Recursos Naturais (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Licenciatura em Tecnologias de Informação (Duração: 4 anos L/PL; Requisitos: 12ª classe/equivalente do SNE)
• Mestrado em Administração e Gestão de Negócios (MBA) (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou outras áreas afins)
• Mestrado em Administração Pública (Duração: 2 anos PL; Requisitos: Lic. em Administração Pública ou áreas afins)
• Mestrado em Sistemas de Informação Geográfica e Monitoria de Recursos Naturais (Duração: 2 anos PL; Requisitos: Lic. em Sistema de Informação ou áreas afins)
• Mestrado em Tecnologias de Informação (Duração: 2 anos PL; Requisitos: Lic. em IT ou áreas afins)
• Mestrado em Gestão e Administração Educacional (Duração: 2 anos PL; Requisitos: Lic. em Gestão ou áreas afins)
• Mestrado em Direito Civil (Duração: 2 anos PL; Requisitos: Lic. em Direito ou outras áreas afins)
EOT;

        return [
            'text' => "🎓 *Bem-vindo(a)!*\nEscolha uma das opções abaixo:",
            'options' => [
                '1. Inscrições e Admissões' => [
                    'text' => 'Inscrições e Admissões — escolha uma opção:',
                    'options' => [
                        '1.1 Como fazer inscrição' =>
                            "Para se inscrever, aceda ao portal de inscrições online, preencha o formulário com os seus dados pessoais e académicos, anexe os documentos exigidos e submeta. Após a submissão, receberá uma confirmação por email.", // TODO: confirmar texto/portal real

                        '1.2 Datas de inscrição' =>
                            "As datas de inscrição variam por curso e por modalidade (presencial ou à distância). Consulte a opção 'Calendário Académico' para os períodos de matrícula/renovação de matrícula de 2026, ou contacte a secretaria do seu campus.",

                        '1.3 Requisitos' =>
                            "Requisitos gerais: para licenciatura, 12ª classe ou equivalente do SNE; para mestrado, grau de licenciatura na área ou área afim; para doutoramento, grau de mestrado na área ou área afim. Além disso, é necessário BI/passaporte válido, fotografias tipo passe e comprovativo de pagamento da taxa de inscrição. Consulte 'Cursos' para ver os requisitos específicos de cada curso.",

                        '1.4 Taxa de inscrição' =>
                            "A taxa de inscrição é de [valor] MT. O pagamento pode ser feito nos balcões autorizados ou por transferência bancária, conforme indicado na opção 'Formas de pagamento'.", // TODO: preencher valor real
                    ],
                ],

                '2. Cursos' => [
                    'text' => 'Cursos — escolha uma categoria:',
                    'options' => [
                        '2.1 Licenciaturas' => $licenciaturasText,
                        '2.2 Mestrados' => $mestradosText,
                        '2.3 Formação contínua' =>
                            "A formação contínua inclui cursos livres, workshops e certificações de curta duração, com inscrições abertas ao longo do ano. Consulte a secretaria do seu campus para o calendário atualizado.", // TODO: sem dados na planilha fornecida

                        '2.4 Cursos por campus' => [
                            'text' => 'Escolha o campus/faculdade para ver os cursos disponíveis:',
                            'options' => [
                                'Ciências de Saúde - Beira' => $campus1Text,
                                'Economia e Gestão - Beira' => $campus2Text,
                                'Engenharia - Chimoio' => $campus3Text,
                                'Rec. Naturais e Mineralogia - Tete' => $campus4Text,
                                'Ciências Sociais e Pol. - Quelimane' => $campus5Text,
                                'Direito - Nampula' => $campus6Text,
                                'Educação e Comunicação - Nampula' => $campus7Text,
                                'Extensão de Maputo' => $campus8Text,
                                'Extensão de Nacala' => $campus9Text,
                                'Extensão de Gurué' => $campus10Text,
                                'Extensão de Xai-Xai' => $campus11Text,
                                'Ciências Agronómicas - Cuamba' => $campus12Text,
                                'Rec. Florestais e Faunísticos - Lichinga' => $campus13Text,
                                'Gestão Turismo e Informática - Pemba' => $campus14Text,
                            ],
                        ],
                    ],
                ],

                '3. Propinas e Pagamentos' => [
                    'text' => 'Propinas e Pagamentos — escolha uma opção:',
                    'options' => [
                        '3.1 Valor das propinas' =>
                            "O valor das propinas varia por curso e nível académico. Os valores atualizados estão disponíveis na secretaria ou no portal do estudante.", // TODO
                        '3.2 Formas de pagamento' =>
                            "Pode pagar as propinas por depósito bancário, transferência ou diretamente nos balcões autorizados. Guarde sempre o comprovativo de pagamento.", // TODO
                        '3.3 Prazo de pagamento' =>
                            "O pagamento das propinas deve ser efetuado até ao dia [dia] de cada mês. Pagamentos fora do prazo estão sujeitos a multa.", // TODO
                        '3.4 Multas' =>
                            "Pagamentos em atraso são sujeitos a uma multa de [valor/percentagem]. Em caso de dificuldades, contacte a secretaria para regularizar a situação.", // TODO
                        '3.5 Confirmar pagamento' =>
                            "Para confirmar um pagamento, envie o número de referência ou comprovativo à secretaria financeira, ou verifique o estado através da opção 'Situação financeira' no menu principal.", // TODO
                    ],
                ],

                '4. Documentos' => [
                    'text' => 'Documentos — escolha uma opção:',
                    'options' => [
                        '4.1 Documentos para inscrição' =>
                            "Para a inscrição são necessários: certificado de habilitações, BI ou passaporte, fotografias tipo passe e comprovativo de pagamento da taxa de inscrição.", // TODO
                        '4.2 Documentos para matrícula' =>
                            "Para a matrícula são necessários: comprovativo de inscrição aprovada, comprovativo de pagamento de propinas e documento de identificação.", // TODO
                        '4.3 Documentos para transferência' =>
                            "Para transferência de outra instituição, são necessários: histórico académico da instituição de origem, certificado de habilitações e carta de transferência/declaração de vaga.", // TODO
                    ],
                ],

                '5. Calendário Académico' => [
                    'text' => 'Calendário Académico 2026 — escolha uma opção:',
                    'options' => [
                        '5.1 Início das aulas' =>
                            "📅 *Calendário Académico 2026*\n\n• Abertura do ano académico: 13/03/2026\n• Início das aulas — Doutoramento e Mestrado: 20/04/2026\n• 1º semestre (Novo Ingresso, modalidade presencial): 09/03/2026 a 10/07/2026\n• 1º semestre (Novo Ingresso, modalidade à distância): 09/03/2026 a 03/07/2026\n\nNota: estudantes internos (2º ao 4º ano) e cursos da Faculdade de Ciências de Saúde têm datas próprias — consulte a secretaria do seu curso para confirmar.",

                        '5.2 Exames' =>
                            "O calendário académico oficial de 2026 não especifica um período único de exames — as avaliações decorrem ao longo de cada semestre letivo, conforme o plano de cada curso. Para as datas exatas dos exames do seu curso, contacte a coordenação de curso ou a secretaria do seu campus.",

                        '5.3 Férias' =>
                            "📅 *Férias semestrais — 2026*\n\n• Modalidade presencial: 15/06/2026 a 17/07/2026\n• Modalidade à distância: 06/07/2026 a 17/07/2026",

                        '5.4 Matrículas' =>
                            "📅 *Matrículas / Renovação de matrícula — 2026*\n\n• Renovação de matrícula (modalidade presencial): 05/01/2026 a 10/02/2026\n• Renovação de matrícula (modalidade à distância): até 30/01/2026\n• Renovação de matrícula — 2º a 4º ano do Curso de Agronomia: 14/12/2025 a 30/01/2026\n\nPara novo ingresso, a matrícula é feita no acto de inscrição — veja a opção 'Inscrições e Admissões'.",
                    ],
                ],

                '6. Contactos' => [
                    'text' => 'Contactos — escolha uma opção:',
                    'options' => [
                        '6.1 Secretaria' =>
                            "Secretaria: telefone [número], horário de atendimento das [hora início] às [hora fim], de segunda a sexta-feira.", // TODO
                        '6.2 Campus' =>
                            "Indique o campus pretendido (Beira, Chimoio, Tete, Quelimane, Nampula, Maputo, Nacala, Gurué, Xai-Xai, Cuamba, Lichinga, Pemba) para receber morada e contacto específico.",
                        '6.3 Email institucional' =>
                            "Pode contactar-nos através do email institucional: [email]. Respondemos normalmente em até 48 horas úteis.", // TODO
                    ],
                ],

                '7. Documentos Académicos' => [
                    'text' => 'Documentos Académicos — escolha uma opção:',
                    'options' => [
                        '7.1 Declaração' =>
                            "As declarações académicas podem ser solicitadas na secretaria mediante requerimento e pagamento da respetiva taxa.", // TODO
                        '7.2 Certificado' =>
                            "O certificado de conclusão de curso é emitido após a finalização de todas as unidades curriculares e regularização financeira junto à secretaria.", // TODO
                        '7.3 Histórico académico' =>
                            "O histórico académico pode ser solicitado na secretaria, mediante requerimento e pagamento da taxa correspondente.", // TODO
                        '7.4 Prazo de emissão' =>
                            "O prazo médio de emissão de documentos académicos é de [X] dias úteis após a submissão do requerimento.", // TODO
                    ],
                ],

                '8. Horário de Atendimento' =>
                    "Horário de atendimento: Segunda a Sexta-feira, das [hora início] às [hora fim]. Aos sábados: [horário, se aplicável].", // TODO

                '9. Atendimento Humano' =>
                    "Vamos encaminhar o seu pedido para um atendente humano. Por favor, aguarde — em breve alguém entrará em contacto consigo.", // TODO: considerar disparar notificação/flag aqui no futuro
            ],
        ];
    }
}