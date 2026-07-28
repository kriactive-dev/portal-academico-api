<?php

namespace Database\Seeders;

use App\Models\ChatBot\OptionBot;
use App\Models\ChatBot\QuestionBot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatBotMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
            // Folha: string simples = resposta final (sem opções na BD)
            if (is_string($child)) {
                $childId = QuestionBot::create([
                    'text'     => $child,
                    'type'     => 'button',
                    'is_start' => false,
                    'active'   => true,
                ])->id;
            } else {
                // Submenu: recursão
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
     * Árvore completa do menu, seguindo exatamente a estrutura que você passou.
     */
    private function menuTree(): array
    {
        return [
            'text' => "🎓 *Bem-vindo(a)!*\nEscolha uma das opções abaixo:",
            'options' => [
                '1. Inscrições e Admissões' => [
                    'text' => 'Inscrições e Admissões — escolha uma opção:',
                    'options' => [
                        '1.1 Como fazer inscrição' =>
                            "Para se inscrever, aceda ao portal de inscrições online, preencha o formulário com os seus dados pessoais e académicos, anexe os documentos exigidos e submeta. Após a submissão, receberá uma confirmação por email.", // TODO: confirmar texto/portal real
 
                        '1.2 Datas de inscrição' =>
                            "As inscrições decorrem normalmente entre [data início] e [data fim] de cada ano académico. Consulte o calendário académico para confirmar as datas exatas do período em curso.", // TODO: preencher datas reais
 
                        '1.3 Requisitos' =>
                            "Requisitos gerais para inscrição: certificado de habilitações (12ª classe ou equivalente para licenciatura, ou grau de licenciatura para mestrado), documento de identificação válido, fotografias tipo passe e comprovativo de pagamento da taxa de inscrição.", // TODO: ajustar por curso
 
                        '1.4 Taxa de inscrição' =>
                            "A taxa de inscrição é de [valor] MT. O pagamento pode ser feito nos balcões autorizados ou por transferência bancária, conforme indicado na opção 'Formas de pagamento'.", // TODO: preencher valor real
                    ],
                ],
 
                '2. Cursos' => [
                    'text' => 'Cursos — escolha uma categoria:',
                    'options' => [
                        '2.1 Licenciaturas' =>
                            "Oferecemos licenciaturas nas áreas de [lista de cursos]. Para mais detalhes sobre grade curricular e duração, contacte a secretaria do seu campus.", // TODO
                        '2.2 Mestrados' =>
                            "Os mestrados disponíveis são: [lista de mestrados]. As inscrições para mestrado seguem calendário próprio, divulgado no início de cada ano letivo.", // TODO
                        '2.3 Formação contínua' =>
                            "A formação contínua inclui cursos livres, workshops e certificações de curta duração, com inscrições abertas ao longo do ano. Consulte a secretaria para o calendário atualizado.", // TODO
                        '2.4 Cursos por campus' =>
                            "Cada campus tem uma oferta formativa própria. Indique o seu campus (Beira, Maputo, etc.) na secretaria ou pelo email institucional para receber a lista de cursos disponíveis nesse local.", // TODO
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
                    'text' => 'Calendário Académico — escolha uma opção:',
                    'options' => [
                        '5.1 Início das aulas' =>
                            "O início das aulas está previsto para [data]. Qualquer alteração será comunicada através dos canais oficiais.", // TODO
                        '5.2 Exames' =>
                            "O período de exames decorre entre [data início] e [data fim]. O calendário detalhado por curso é publicado antes do início do período.", // TODO
                        '5.3 Férias' =>
                            "As férias académicas decorrem entre [data início] e [data fim], conforme o calendário académico oficial.", // TODO
                        '5.4 Matrículas' =>
                            "O período de matrículas decorre entre [data início] e [data fim]. Após este prazo, poderá haver taxa adicional ou vaga sujeita a disponibilidade.", // TODO
                    ],
                ],
 
                '6. Contactos' => [
                    'text' => 'Contactos — escolha uma opção:',
                    'options' => [
                        '6.1 Secretaria' =>
                            "Secretaria: telefone [número], horário de atendimento das [hora início] às [hora fim], de segunda a sexta-feira.", // TODO
                        '6.2 Campus' =>
                            "Indique o campus pretendido (Beira, Maputo, etc.) para receber morada e contacto específico.", // TODO
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
