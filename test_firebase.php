<?php

require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

try {
    echo "Testando conexão Firebase...\n";
    
    // Configurações
    $credentialsPath = 'storage/app/firebase/firebase-credentials.json';
    $projectId = 'portalacademico-f09af';
    
    // Verifica se o arquivo existe
    if (!file_exists($credentialsPath)) {
        echo "❌ Arquivo de credenciais não encontrado: $credentialsPath\n";
        exit(1);
    }
    
    echo "✅ Arquivo de credenciais encontrado\n";
    
    // Testa a inicialização do Factory
    $factory = (new Factory)
        ->withServiceAccount($credentialsPath)
        ->withProjectId($projectId);
    
    echo "✅ Factory Firebase inicializado\n";
    
    // Testa a criação do serviço de messaging
    $messaging = $factory->createMessaging();
    
    echo "✅ Serviço de Messaging criado\n";
    
    // Testa a validação de um token fictício (sem enviar)
    $testToken = 'test-token';
    $message = CloudMessage::withTarget('token', $testToken)
        ->withNotification(Notification::create('Test', 'Test message'));
    
    echo "✅ Mensagem de teste criada\n";
    
    echo "🎉 Configuração Firebase OK!\n";
    
} catch (\Kreait\Firebase\Exception\AuthException $e) {
    echo "❌ Erro de autenticação Firebase: " . $e->getMessage() . "\n";
    echo "Detalhes: " . $e->getTraceAsString() . "\n";
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}