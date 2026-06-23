<?php

namespace App\Services\Student;

use App\Services\EnvironmentVariable\EnvironmentVariableService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StudentInformationService
{
    /**
     * Create a new class instance.
     */
    protected EnvironmentVariableService $environmentVariableService;

    public function __construct(EnvironmentVariableService $environmentVariableService)
    {
        $this->environmentVariableService = $environmentVariableService;
    }

    public function getAcademicInformation(int $studentCode): ?array
    {
        try {
            // Busca a chave da UCM nas variáveis de ambiente
            $chave = $this->environmentVariableService->getValue('UCM_API_KEY', '0F5DD14AE2E38C7EBD8814D29CF6F6F0'); 
            $codigoEstudante = $studentCode;
            $hash = md5($chave . $codigoEstudante);

            // URL do endpoint da UCM
            // $url = "https://esura.ucm.ac.mz/eSURA/campusOnline/getFullResultsByStudentCode/{$codigoEstudante}/{$hash}";
            $url = "https://172.20.0.123/eSURA/campusOnline/getFullResultsByStudentCode/{$codigoEstudante}/{$hash}";
            
            Log::info('Fazendo requisição para UCM', [
                'codigo_estudante' => $codigoEstudante,
                'url' => $url
            ]);

            // Fazer requisição HTTP com timeout
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // Desabilita verificação SSL se necessário
                ])
                ->get($url);

            // Verificar se a requisição foi bem-sucedida
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Resposta recebida da UCM', [
                    'codigo_estudante' => $codigoEstudante,
                    'status' => $response->status()
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Situação acadêmica obtida com sucesso.'
                ];
            } else {
                Log::error('Erro na requisição para UCM', [
                    'codigo_estudante' => $codigoEstudante,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Erro ao obter situação acadêmica: HTTP ' . $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (Exception $e) {
            Log::error('Exceção ao buscar situação acadêmica', [
                'codigo_estudante' => $studentCode,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno ao buscar situação acadêmica.',
                'error' => $e->getMessage()
            ];
        }
    }  

    public function getFinancialInformation(int $studentCode): ?array
    {
        try {
            // Busca a chave da UCM nas variáveis de ambiente
            $chave = $this->environmentVariableService->getValue('UCM_API_KEY', '0F5DD14AE2E38C7EBD8814D29CF6F6F0'); 
            $codigoEstudante = $studentCode;
            $hash = md5($chave . $codigoEstudante);

            // URL do endpoint da UCM
            // $url = "https://primaveraapi.ucm.ac.mz/api/extracto/{$codigoEstudante}/{$hash}";
            $url = "http://172.20.0.45/api/extracto/{$codigoEstudante}/{$hash}";

            
            Log::info('Fazendo requisição para UCM', [
                'codigo_estudante' => $codigoEstudante,
                'url' => $url
            ]);

            // Fazer requisição HTTP com timeout
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // Desabilita verificação SSL se necessário
                ])
                ->get($url);

            // Verificar se a requisição foi bem-sucedida
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Resposta recebida da UCM', [
                    'codigo_estudante' => $codigoEstudante,
                    'status' => $response->status()
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Situação financeira obtida com sucesso.'
                ];
            } else {
                Log::error('Erro na requisição para UCM', [
                    'codigo_estudante' => $codigoEstudante,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Erro ao obter situação financeira: HTTP ' . $response->status(),
                    'error' => $response->body()
                ];
            }

        } catch (Exception $e) {
            Log::error('Exceção ao buscar situação financeira', [
                'codigo_estudante' => $studentCode,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno ao buscar situação acadêmica.',
                'error' => $e->getMessage()
            ];
        }
    } 

    public function getPersonalInformation(int $studentCode): ?array
    {
        $endpointUrl = config('services.user_enrichment.url');
        $apiKey = config('services.user_enrichment.api_key');
        $timeout = config('services.user_enrichment.timeout', 30);
        
        if (empty($endpointUrl)) {
            Log::warning('UserProfile enrichment: Endpoint URL not configured');
            return null;
        }

        try {
            // Construir URL completa: endpoint/codigo da UCM
            $fullUrl = rtrim($endpointUrl, '/') . '/' . $studentCode;
            
            $httpClient = Http::timeout($timeout);
            
            // Desabilitar verificação SSL em desenvolvimento, 
            if (config('app.env') !== 'production') {
                $httpClient = $httpClient->withOptions(['verify' => false]);
            }
            
            $response = $httpClient
                ->when($apiKey, function ($http) use ($apiKey) {
                    return $http->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'X-API-Key' => $apiKey
                    ]);
                })
                ->get($fullUrl);

            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }

    
}
