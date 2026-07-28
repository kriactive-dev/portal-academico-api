<?php

namespace Database\Seeders;

use App\Models\ChatBot\OptionBot;
use App\Models\ChatBot\QuestionBot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

    private function buildNode(array $node, bool $isStart = false): int
    {
        $question = QuestionBot::create([
            'text'     => $node['text'],
            'type'     => 'button',
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

    private function menuTree(): array
    {
        return [
            'text' => "🎓 *Bem-vindo(a)!*\nEscolha uma das opções abaixo:",
            'options' => [
                '1. Inscrições e Admissões' => [
                    'text' => 'Inscrições e Admissões — escolha uma opção:',
                    'options' => [
                        '1.1 Como fazer inscrição' =>
                            "Para se inscrever, aceda ao portal de inscrições online, preencha o formulário com os seus dados pessoais e académicos, anexe os documentos exigidos e submeta. Após a submissão, receberá uma confirmação por email.",

                        '1.2 Datas de inscrição' =>
                            "As datas de inscrição variam por curso e por modalidade. Consulte a opção 'Calendário Académico' para os períodos de 2026, ou contacte a secretaria do seu campus.",

                        '1.3 Requisitos' =>
                            "Requisitos gerais: 12ª classe/SNE (licenciatura); Licenciatura (mestrado); Mestrado (doutoramento). BI/passaporte, fotos e comprovativo de pagamento.",

                        '1.4 Taxa de inscrição' =>
                            "A taxa de inscrição é de [valor] MT. Pagamento nos balcões autorizados ou por transferência bancária.",
                    ],
                ],

                '2. Cursos' => [
                    'text' => 'Cursos — escolha uma categoria:',
                    'options' => [
                        '2.1 Licenciaturas' => $this->getLicenciaturasText(),
                        '2.2 Mestrados' => $this->getMestradosText(),
                        '2.3 Formação contínua' =>
                            "Cursos livres, workshops e certificações de curta duração durante o ano. Consulte a secretaria do seu campus.",

                        '2.4 Cursos por campus' => [
                            'text' => 'Escolha o campus/faculdade:',
                            'options' => $this->getCampusOptions(),
                        ],
                    ],
                ],

                '3. Propinas e Pagamentos' => [
                    'text' => 'Propinas e Pagamentos — escolha uma opção:',
                    'options' => [
                        '3.1 Valor das propinas' =>
                            "Valores variam por curso/nível. Consulte a secretaria ou portal do estudante.",
                        '3.2 Formas de pagamento' =>
                            "Depósito bancário, transferência ou balcões autorizados. Guarde o comprovativo.",
                        '3.3 Prazo de pagamento' =>
                            "Até ao dia [dia] de cada mês. Fora do prazo sujeito a multa.",
                        '3.4 Multas' =>
                            "Pagamentos em atraso: multa de [valor/percentagem]. Contacte a secretaria.",
                        '3.5 Confirmar pagamento' =>
                            "Envie referência/comprovativo à secretaria ou verifique em 'Situação financeira'.",
                    ],
                ],

                '4. Documentos' => [
                    'text' => 'Documentos — escolha uma opção:',
                    'options' => [
                        '4.1 Documentos para inscrição' =>
                            "Certificado de habilitações, BI/passaporte, fotos e comprovativo de pagamento.",
                        '4.2 Documentos para matrícula' =>
                            "Comprovativo de inscrição aprovada, comprovativo de pagamento e documento de identificação.",
                        '4.3 Documentos para transferência' =>
                            "Histórico académico, certificado de habilitações e carta de transferência.",
                    ],
                ],

                '5. Calendário Académico' => [
                    'text' => 'Calendário Académico 2026:',
                    'options' => [
                        '5.1 Início das aulas' =>
                            "📅 Abertura: 13/03/2026\nInício aulas Doutorado/Mestrado: 20/04/2026\n1º Semestre Presencial: 09/03 a 10/07/2026\n1º Semestre EAD: 09/03 a 03/07/2026",

                        '5.2 Exames' =>
                            "Avaliações ao longo do semestre. Datas específicas: contacte a coordenação do curso.",

                        '5.3 Férias' =>
                            "📅 Férias semestrais 2026\nPresencial: 15/06 a 17/07/2026\nEAD: 06/07 a 17/07/2026",

                        '5.4 Matrículas' =>
                            "📅 Matrículas 2026\nRenovação Presencial: 05/01 a 10/02/2026\nRenovação EAD: até 30/01/2026",
                    ],
                ],

                '6. Contactos' => [
                    'text' => 'Contactos — escolha uma opção:',
                    'options' => [
                        '6.1 Secretaria' =>
                            "Telefone [número], atendimento [hora] às [hora], Segunda a Sexta.",
                        '6.2 Campus' =>
                            "Envie o campus: Beira, Chimoio, Tete, Quelimane, Nampula, Maputo, Nacala, Gurué, Xai-Xai, Cuamba, Lichinga, Pemba.",
                        '6.3 Email institucional' =>
                            "Email: [email]. Resposta em até 48h úteis.",
                    ],
                ],

                '7. Documentos Académicos' => [
                    'text' => 'Documentos Académicos:',
                    'options' => [
                        '7.1 Declaração' =>
                            "Solicitar na secretaria com requerimento e pagamento de taxa.",
                        '7.2 Certificado' =>
                            "Emissão após conclusão do curso e regularização financeira.",
                        '7.3 Histórico académico' =>
                            "Solicitar na secretaria com requerimento e pagamento de taxa.",
                        '7.4 Prazo de emissão' =>
                            "Prazo médio: [X] dias úteis após o requerimento.",
                    ],
                ],

                '8. Horário de Atendimento' =>
                    "Segunda a Sexta: [hora] às [hora]. Sábados: [horário].",

                '9. Atendimento Humano' =>
                    "Vamos encaminhar para um atendente. Por favor, aguarde.",
            ],
        ];
    }

    private function getLicenciaturasText(): string
    {
        return <<<'EOT'
🎓 *Licenciaturas UCM* (oferta varia por campus):

• Eng. Alimentar • Eng. Civil • Eng. Electrotécnica • Eng. Geológica • Eng. Mecânica • Eng. Minas • Eng. Processamento Mineral • Administração Pública • Ciências Pol. e Rel. Internacionais • Contabilidade e Auditoria • Tecnologias Informação • Desenv. Comunitário e Serviço Social • Psicopedagogia • Administração Hospitalar • Administração e Gestão Hospitalar • Administração e Gestão de Empresas • Agronomia • Análises Clínicas e Laboratoriais • Arquitectura • Criminologia e Justiça Criminal • Direito • Economia e Gestão • Enfermagem Superior • Farmácia • Gestão Ambiental • Gestão Portuária • Gestão Recursos Florestais e Faunísticos • Gestão Recursos Humanos • Gestão RH e Relações Laborais • Gestão Relações Públicas e Marketing • Gestão Meio Ambiente e Recursos Naturais • Gestão e Administração Educacional • Medicina Geral • Psicologia Clínica e Assistência Social

Consulte "Cursos por campus" para oferta disponível.
EOT;
    }

    private function getMestradosText(): string
    {
        return <<<'EOT'
🎓 *Mestrados UCM* (oferta varia por campus):

• Contabilidade e Auditoria • Penal • Administração Pública • MBA • Ciência Política: Governação e RI • Comunicação para Desenvolvimento • Desenv. Sustentável Recursos Florestais • Direito Administrativo • Direito Civil • Direito Constitucional • Direito Empresarial • Direito Fiscal • Direito Penal • Direito Petróleo e Gás • Direito Trabalho • Direito e Desenv. Sustentável • Direitos Humanos, Justiça e Paz • Economia • Gestão Projectos Desenvolvimento • Gestão RH • Gestão e Administração Educacional • Gestão Relações Públicas e Marketing • Planeamento e Desenv. Regional • Psicologia Saúde • Psicopedagogia • Saúde Pública • SIG e Monitoria Recursos Naturais • Solos e Agricultura Sustentável • Tecnologias Informação

*Doutoramentos:* Comunicação • Direito Privado • Direito Público • Inovação Educativa

Consulte "Cursos por campus" para oferta disponível.
EOT;
    }

    private function getCampusOptions(): array
    {
        // Dividido em grupos para não exceder 10 opções
        return [
            'Centro-Norte (10)' => [
                'text' => '🏛️ Campus Centro-Norte:',
                'options' => [
                    'Ciências Saúde - Beira' => $this->getCampus1Text(),
                    'Economia e Gestão - Beira' => $this->getCampus2Text(),
                    'Engenharia - Chimoio' => $this->getCampus3Text(),
                    'Rec. Naturais - Tete' => $this->getCampus4Text(),
                    'Ciências Sociais - Quelimane' => $this->getCampus5Text(),
                    'Direito - Nampula' => $this->getCampus6Text(),
                    'Educação - Nampula' => $this->getCampus7Text(),
                    'Nacala' => $this->getCampus9Text(),
                    'Gurué' => $this->getCampus10Text(),
                    'Cuamba' => $this->getCampus12Text(),
                ],
            ],
            'Sul-Norte (4)' => [
                'text' => '🏛️ Campus Sul-Norte:',
                'options' => [
                    'Maputo' => $this->getCampus8Text(),
                    'Xai-Xai' => $this->getCampus11Text(),
                    'Lichinga' => $this->getCampus13Text(),
                    'Pemba' => $this->getCampus14Text(),
                ],
            ],
        ];
    }

    // Textos encurtados dos campi (mantendo informações essenciais)
    private function getCampus1Text(): string
    {
        return "📍 *Ciências Saúde - Beira*\n\nMedicina Geral (6a/L; 12ª/SNE) • Enfermagem Superior (4a L/PL; 12ª/SNE) • Adm. Hospitalar (4a L/PL; 12ª/SNE) • Farmácia (4a/L; 12ª/SNE) • Análises Clínicas (4a L/PL; 12ª/SNE) • Psicologia Clínica (12ª/SNE)\n\nMestrados: Saúde Pública (2a PL; Lic. Saúde) • Psicologia Saúde (2a PL; Lic. Saúde)";
    }

    private function getCampus2Text(): string
    {
        return "📍 *Economia e Gestão - Beira*\n\nDireito (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Adm. Pública (4a L/PL) • Arquitectura (5a/L) • Adm. e Gestão Empresas (4a L/PL) • Gestão RH (4a L/PL) • Gestão Portuária (4a L/PL) • TI (4a L/PL) • Economia e Gestão (4a L/PL)\n\nMestrados: MBA • Dir. Administrativo • Dir. Empresarial • Dir. Penal • Contabilidade e Auditoria • Gestão RH • SIG e Monitoria RN • Economia • Planeamento e Desenv. Regional";
    }

    private function getCampus3Text(): string
    {
        return "📍 *Engenharia - Chimoio*\n\nAdm. Pública (4a L/PL) • Agronomia (5a/L) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL) • Economia e Gestão (4a L/PL) • TI (4a L/PL) • Psicologia Clínica (4a/L) • Eng. Alimentar (5a L/PL) • Eng. Civil (5a L/PL) • Eng. Electrotécnica (5a L/PL) • Eng. Mecânica (5a L/PL)\n\nMestrados: MBA • Adm. Pública • Dir. Administrativo • Gestão e Adm. Educacional";
    }

    private function getCampus4Text(): string
    {
        return "📍 *Rec. Naturais e Mineralogia - Tete*\n\nAdm. Pública (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL) • Economia e Gestão (4a L/PL) • Gestão Ambiental (4a L/PL) • Gestão RH (4a L/PL) • TI (4a L/PL) • Eng. Processamento Mineral (5a L/PL) • Eng. Minas (5a L/PL) • Eng. Geológica (5a L/PL)\n\nMestrados: MBA • Adm. Pública • Dir. Empresarial • Gestão Projectos Desenv. • Gestão e Adm. Educacional";
    }

    private function getCampus5Text(): string
    {
        return "📍 *Ciências Sociais e Políticas - Quelimane*\n\nAdm. e Gestão Empresas (4a L/PL) • Adm. e Gestão Hospitalar (4a L/PL) • Adm. Pública (4a L/PL) • Ciências Pol. e RI (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Desenv. Comunitário e Serviço Social (4a L/PL) • Direito (4a L/PL) • Economia e Gestão (4a L/PL) • Gestão RH (4a L/PL) • TI (4a L/PL)\n\nMestrados: MBA • Adm. Pública • TI • Ciência Política • Dir. Administrativo • Gestão e Adm. Educacional • Gestão Projectos Desenv. • Contabilidade e Auditoria • Saúde Pública";
    }

    private function getCampus6Text(): string
    {
        return "📍 *Direito - Nampula*\n\nDireito (4a L/PL) • Adm. Pública (4a L/PL) • Ciências Pol. e RI (4a L/PL) • TI (4a L/PL) • Criminologia e Justiça Criminal (4a L/PL)\n\nMestrados: Adm. Pública • Dir. Civil • Dir. e Desenv. Sustentável • Dir. Fiscal • Penal • Ciência Política • Dir. Empresarial • Dir. Petróleo e Gás • Dir. Constitucional • Dir. Trabalho\n\nDoutoramentos: Direito Público • Direito Privado";
    }

    private function getCampus7Text(): string
    {
        return "📍 *Educação e Comunicação - Nampula*\n\nPsicopedagogia (4a L/PL) • Gestão e Adm. Educacional (4a L/PL) • Gestão Relações Públicas e Marketing (4a L/PL) • Economia e Gestão (4a L/PL) • Desenv. Comunitário e Serviço Social (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Gestão RH e Relações Laborais (4a L/PL)\n\nMestrados: Gestão RH • Gestão Relações Públicas • Comunicação para Desenv. • Gestão Projectos Desenv. • MBA\n\nDoutoramentos: Inovação Educativa • Ciências da Comunicação";
    }

    private function getCampus8Text(): string
    {
        return "📍 *Extensão Maputo*\n\nAdm. Pública (4a L/PL) • Ciências Pol. e RI (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL) • Economia e Gestão (4a L/PL)\n\nMestrados: MBA • Dir. Administrativo • Contabilidade e Auditoria • Saúde Pública";
    }

    private function getCampus9Text(): string
    {
        return "📍 *Extensão Nacala*\n\nGestão Portuária (4a L/PL) • Gestão RH (4a L/PL) • Direito (4a L/PL) • Contabilidade e Auditoria (4a L/PL)\n\nMestrados: MBA • Gestão e Adm. Educacional • Gestão RH";
    }

    private function getCampus10Text(): string
    {
        return "📍 *Extensão Gurué*\n\nAdm. Pública (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL)\n\nMestrados: Adm. Pública • Gestão e Adm. Educacional • Psicopedagogia";
    }

    private function getCampus11Text(): string
    {
        return "📍 *Extensão Xai-Xai*\n\nMestrados: MBA • Direitos Humanos, Justiça e Paz • Ciência Política • Contabilidade e Auditoria";
    }

    private function getCampus12Text(): string
    {
        return "📍 *Ciências Agronómicas - Cuamba*\n\nAdm. Pública (4a L/PL) • Agronomia (4a L/PL) • Direito (4a L/PL)\n\nMestrados: Solos e Agricultura Sustentável • MBA";
    }

    private function getCampus13Text(): string
    {
        return "📍 *Rec. Florestais e Faunísticos - Lichinga*\n\nEconomia e Gestão (4a L/PL) • Gestão Recursos Florestais (4a L/PL) • Adm. e Gestão Hospitalar (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL)\n\nMestrados: MBA • Gestão e Adm. Educacional • Dir. Administrativo • Desenv. Sustentável Recursos Florestais";
    }

    private function getCampus14Text(): string
    {
        return "📍 *Gestão Turismo e Informática - Pemba*\n\nAdm. Pública (4a L/PL) • Contabilidade e Auditoria (4a L/PL) • Direito (4a L/PL) • Economia e Gestão (4a L/PL) • Gestão RH (4a L/PL) • Gestão Meio Ambiente (4a L/PL) • TI (4a L/PL)\n\nMestrados: MBA • Adm. Pública • SIG e Monitoria RN • TI • Gestão e Adm. Educacional • Dir. Civil";
    }
}