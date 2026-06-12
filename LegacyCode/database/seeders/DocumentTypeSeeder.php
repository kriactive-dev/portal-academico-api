<?php

namespace Database\Seeders;

use App\Models\Documents\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            // Pedidos de Matrícula e Inscrição
            'Pedido de Matrícula',
            'Pedido de Renovação de Matrícula',
            'Pedido de Cancelamento de Matrícula',
            'Pedido de Transferência de Curso',
            'Pedido de Transferência Externa',
            'Pedido de Mudança de Turno',
            'Pedido de Inscrição em Disciplina',
            'Pedido de Cancelamento de Disciplina',
            
            // Certificados e Diplomas
            'Pedido de Certificado de Habilitações',
            'Pedido de Diploma',
            'Pedido de Duplicata de Diploma',
            'Pedido de Certificado de Conclusão de Curso',
            'Pedido de Declaração de Matrícula',
            'Pedido de Declaração de Frequência',
            'Pedido de Atestado de Matrícula',
            'Pedido de Carta de Curso',
            
            // Notas e Avaliações
            'Pedido de Pauta de Notas',
            'Pedido de Certidão de Notas',
            'Pedido de Revisão de Nota',
            'Pedido de Recurso de Avaliação',
            'Pedido de Exame de Recurso',
            'Pedido de Exame de Melhoria',
            'Pedido de Segunda Chamada',
            'Pedido de Prova Substitutiva',
            
            // Exames Especiais
            'Pedido de Exame de Equivalência',
            'Pedido de Reconhecimento de Créditos',
            'Pedido de Aproveitamento de Estudos',
            'Pedido de Validação de Disciplinas',
            'Pedido de Creditação de Experiência Profissional',
            'Pedido de Isenção de Disciplina',
            
            // Documentos Financeiros
            'Pedido de Isenção de Propinas',
            'Pedido de Redução de Propinas',
            'Pedido de Plano de Pagamento',
            'Pedido de Restituição de Taxas',
            'Declaração de Quitação Financeira',
            'Pedido de Bolsa de Estudo',
            
            // Estágios e Projetos
            'Pedido de Estágio Curricular',
            'Pedido de Estágio Profissional',
            'Relatório de Estágio',
            'Pedido de Orientador de Tese',
            'Mudança de Orientador',
            'Projeto de Licenciatura',
            'Dissertação de Mestrado',
            'Tese de Doutoramento',
            
            // Recursos e Reclamações
            'Recurso Acadêmico',
            'Reclamação sobre Serviços',
            'Pedido de Esclarecimento',
            'Queixa Formal',
            'Recurso Disciplinar',
            
            // Documentos Administrativos
            'Pedido de Cartão de Estudante',
            'Pedido de Segunda Via de Cartão',
            'Pedido de Credencial para Biblioteca',
            'Pedido de Acesso a Laboratórios',
            'Autorização para Investigação',
            'Pedido de Sala para Evento',
            
            // Saúde e Apoio Social
            'Atestado Médico',
            'Pedido de Apoio Psicológico',
            'Declaração para Seguro',
            'Pedido de Apoio Social',
            'Justificação de Faltas por Doença',
            
            // Mobilidade e Intercâmbio
            'Pedido de Mobilidade Estudantil',
            'Candidatura a Programa de Intercâmbio',
            'Reconhecimento de Estudos no Exterior',
            'Learning Agreement',
            'Transcript of Records',
            
            // Pós-Graduação
            'Candidatura a Mestrado',
            'Candidatura a Doutoramento',
            'Pedido de Prorrogação de Prazo',
            'Mudança de Tema de Investigação',
            'Pedido de Coorientador',
            'Marcação de Defesa de Tese',
            
            // Documentos de Identificação
            'Pedido de Passe Estudantil',
            'Declaração para Visto',
            'Carta de Recomendação',
            'Referência Académica',
            'Declaração de Bom Comportamento',
            
            // Outros Serviços
            'Pedido de Informação Geral',
            'Solicitação de Documentos Diversos',
            'Pedido de Cópia de Processo',
            'Autorização de Saída de Material',
            'Pedido de Uso de Instalações',
            'Registo de Actividade Extracurricular',
        ];

        foreach ($documentTypes as $typeName) {
            DocumentType::updateOrCreate(
                ['name' => $typeName],
                [
                    'name' => $typeName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Tipos de documentos criados com sucesso!');
        $this->command->info('Total de tipos: ' . count($documentTypes));
    }
}
