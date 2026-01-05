<?php

namespace Database\Seeders;

use App\Models\EnvironmentVariable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnvironmentVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variables = [
            // Tokens de APIs
            [
                'key' => 'UCM_API_KEY',
                'value' => '0F5DD14AE2E38C7EBD8814D29CF6F6F0',
                'description' => 'Chave de acesso à API da UCM para situação acadêmica',
                'category' => 'integrations',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'key' => 'OPENAI_API_KEY',
                'value' => 'sk-proj-...',
                'description' => 'Chave da API do OpenAI para ChatGPT',
                'category' => 'tokens',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'key' => 'FIREBASE_SERVER_KEY',
                'value' => 'AAAAexample...',
                'description' => 'Chave do servidor Firebase para notificações push',
                'category' => 'tokens',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'key' => 'GOOGLE_MAPS_API_KEY',
                'value' => 'AIzaSyExample...',
                'description' => 'Chave da API do Google Maps',
                'category' => 'tokens',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            
            // Configurações de integração
            [
                'key' => 'WHATSAPP_API_URL',
                'value' => 'https://api.whatsapp.com/v1',
                'description' => 'URL da API do WhatsApp Business',
                'category' => 'integrations',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'key' => 'PAYMENT_GATEWAY_URL',
                'value' => 'https://api.pagamento.com/v2',
                'description' => 'URL do gateway de pagamento',
                'category' => 'integrations',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            
            // Configurações do sistema
            [
                'key' => 'MAX_FILE_UPLOAD_SIZE',
                'value' => '10',
                'description' => 'Tamanho máximo de upload de arquivo em MB',
                'category' => 'system',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'key' => 'ENABLE_EMAIL_NOTIFICATIONS',
                'value' => 'true',
                'description' => 'Habilitar notificações por email',
                'category' => 'system',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'key' => 'MAINTENANCE_MODE',
                'value' => 'false',
                'description' => 'Modo de manutenção do sistema',
                'category' => 'system',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            
            // Configurações de segurança
            [
                'key' => 'JWT_SECRET_BACKUP',
                'value' => 'your-backup-secret-key',
                'description' => 'Chave secreta de backup para JWT',
                'category' => 'security',
                'is_encrypted' => true,
                'is_active' => false,
            ],
            [
                'key' => 'API_RATE_LIMIT',
                'value' => '100',
                'description' => 'Limite de requisições por minuto por IP',
                'category' => 'security',
                'is_encrypted' => false,
                'is_active' => true,
            ],
        ];

        foreach ($variables as $variable) {
            EnvironmentVariable::updateOrCreate(
                ['key' => $variable['key']],
                $variable
            );
        }

        $this->command->info('Variáveis de ambiente criadas com sucesso!');
    }
}
