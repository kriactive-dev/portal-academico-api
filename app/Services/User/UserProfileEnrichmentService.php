<?php

namespace App\Services\User;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class UserProfileEnrichmentService
{
    /**
     * Enriquecer o perfil do usuário com dados externos
     *
     * @param UserProfile $userProfile
     * @return bool
     */
    public function enrichProfile(UserProfile $userProfile): bool
    {
        try {
            // Obter o email do usuário
            $userEmail = $userProfile->user->email;
            
            if (empty($userEmail)) {
                Log::warning('UserProfile enrichment skipped: User email is empty', [
                    'user_profile_id' => $userProfile->id,
                    'user_id' => $userProfile->user_id
                ]);
                return false;
            }

            // Verificar se é email da UCM (@ucm.ac.mz)
            if (!$this->isUCMEmail($userEmail)) {
                Log::info('UserProfile enrichment skipped: Not UCM email', [
                    'user_profile_id' => $userProfile->id,
                    'user_id' => $userProfile->user_id,
                    'email' => $userEmail
                ]);
                return false;
            }

            // Extrair código do estudante do email
            $studentCode = $this->extractStudentCodeFromEmail($userEmail);
            
            if (empty($studentCode)) {
                Log::warning('UserProfile enrichment skipped: Could not extract student code', [
                    'user_profile_id' => $userProfile->id,
                    'user_id' => $userProfile->user_id,
                    'email' => $userEmail
                ]);
                return false;
            }

            // Fazer consulta ao endpoint externo usando o código
            $externalData = $this->fetchUserDataFromEndpoint($studentCode);
            
            if (!$externalData) {
                Log::info('UserProfile enrichment: No external data found for student', [
                    'user_profile_id' => $userProfile->id,
                    'student_code' => $studentCode,
                    'email' => $userEmail
                ]);
                return false;
            }

            // Atualizar o perfil com os dados encontrados
            return $this->updateProfileWithExternalData($userProfile, $externalData);
            
        } catch (Exception $e) {
            Log::error('UserProfile enrichment failed', [
                'user_profile_id' => $userProfile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Verificar se o email é da UCM (@ucm.ac.mz)
     *
     * @param string $email
     * @return bool
     */
    private function isUCMEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), '@ucm.ac.mz');
    }

    /**
     * Extrair código do estudante do email UCM
     * Ex: 20210123@ucm.ac.mz -> 20210123
     *
     * @param string $email
     * @return string|null
     */
    private function extractStudentCodeFromEmail(string $email): ?string
    {
        if (!$this->isUCMEmail($email)) {
            return null;
        }

        $parts = explode('@', $email);
        $studentCode = $parts[0] ?? null;

        // Validar se o código parece válido (apenas números e/ou letras)
        if ($studentCode && preg_match('/^[a-zA-Z0-9]+$/', $studentCode)) {
            return $studentCode;
        }

        return null;
    }

    /**
     * Fazer consulta ao endpoint externo
     *
     * @param string $studentCode
     * @return array|null
     */
    private function fetchUserDataFromEndpoint(string $studentCode): ?array
    {
        $endpointUrl = config('services.user_enrichment.url');
        $apiKey = config('services.user_enrichment.api_key');
        $timeout = config('services.user_enrichment.timeout', 10);
        
        if (empty($endpointUrl)) {
            Log::warning('UserProfile enrichment: Endpoint URL not configured');
            return null;
        }

        try {
            // Construir URL completa: endpoint/codigo
            $fullUrl = rtrim($endpointUrl, '/') . '/' . $studentCode;
            
            $httpClient = Http::timeout($timeout);
            
            // Desabilitar verificação SSL em desenvolvimento
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
                $data = $response->json();
                
                Log::info('UserProfile enrichment: External API response received', [
                    'student_code' => $studentCode,
                    'endpoint' => $fullUrl,
                    'status_code' => $response->status(),
                    'has_data' => !empty($data)
                ]);
                
                return $this->extractUserData($data);
            }

            Log::warning('UserProfile enrichment: External API request failed', [
                'student_code' => $studentCode,
                'endpoint' => $fullUrl,
                'status_code' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;
            
        } catch (Exception $e) {
            Log::error('UserProfile enrichment: HTTP request failed', [
                'student_code' => $studentCode,
                'endpoint' => $endpointUrl,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Extrair dados relevantes da resposta da API UCM
     *
     * @param array $apiResponse
     * @return array|null
     */
    private function extractUserData(array $apiResponse): ?array
    {
        // Verificar se a resposta foi bem-sucedida
        if (!isset($apiResponse['status']) || $apiResponse['status'] !== 'SUCCESS') {
            Log::info('UCM API response indicates failure', [
                'status' => $apiResponse['status'] ?? 'unknown',
                'result' => $apiResponse['result'] ?? 'no result'
            ]);
            return null;
        }

        // Obter dados do estudante da UCM
        $userData = $apiResponse['result'] ?? null;

        // Verificar se existem dados úteis
        if (empty($userData) || !is_array($userData)) {
            return null;
        }

        // Mapear campos da API UCM para o UserProfile
        $extractedData = [];
        
        // Telefone/Contacto
        if (!empty($userData['stundentcode'])) {
            $extractedData['stundent_code'] = $userData['stundentcode'];
        }
        if (!empty($userData['contacto'])) {
            $extractedData['phone'] = $userData['contacto'];
        }

        if (!empty($userData['nome'])) {
            $extractedData['full_name'] = $userData['nome'];
        }
        
        // Endereço/Localização
        if (!empty($userData['localidade'])) {
            $extractedData['city'] = $userData['localidade'];
        }
        
        if (!empty($userData['provincia'])) {
            $extractedData['state'] = $userData['provincia'];
        }
        
        // Combinar cidade e província para endereço completo
        if (!empty($userData['localidade']) && !empty($userData['provincia'])) {
            $extractedData['address'] = $userData['localidade'] . ', ' . $userData['provincia'];
        } elseif (!empty($userData['localidade'])) {
            $extractedData['address'] = $userData['localidade'];
        } elseif (!empty($userData['provincia'])) {
            $extractedData['address'] = $userData['provincia'];
        }
        
        // Data de nascimento (converter formato: "18-9-1997" para "1997-09-18")
        if (!empty($userData['datanasc'])) {
            $extractedData['date_of_birth'] = $this->convertUCMDateFormat($userData['datanasc']);
        }
        
        // Gênero
        if (!empty($userData['sexo'])) {
            $extractedData['gender'] = $userData['sexo'];
        }
        
        // Informações acadêmicas como profissão/bio
        if (!empty($userData['curso'])) {
            $extractedData['profession'] = 'Estudante - ' . $userData['curso'];
        }
        
        if (!empty($userData['nomeFaculdade'])) {
            $extractedData['company'] = $userData['nomeFaculdade'];
        }
        
        // Bio combinando informações acadêmicas
        $bioInfo = [];
        if (!empty($userData['curso'])) {
            $bioInfo[] = 'Curso: ' . $userData['curso'];
        }
        if (!empty($userData['anoDeFrequencia'])) {
            $bioInfo[] = 'Ano: ' . $userData['anoDeFrequencia'] . 'º';
        }
        if (!empty($userData['unidadeOrganica'])) {
            $bioInfo[] = 'Faculdade: ' . $userData['unidadeOrganica'];
        }
        if (!empty($userData['estado'])) {
            $bioInfo[] = 'Status: ' . $userData['estado'];
        }
        
        if (!empty($bioInfo)) {
            $extractedData['bio'] = implode(' | ', $bioInfo);
        }
        
        // País/Nacionalidade
        if (!empty($userData['nacionalidade'])) {
            $extractedData['country'] = $userData['nacionalidade'];
        }

        if (!empty($userData['nomedopai'])) {
            $extractedData['father_name'] = $userData['nomedopai'];
        }

        if (!empty($userData['nomedamae'])) {
            $extractedData['mother_name'] = $userData['nomedamae'];
        }

        if (!empty($userData['faculdade'])) {
            $extractedData['faculdade'] = $userData['faculdade'];
        }

        if (!empty($userData['unidade_organica'])) {
            $extractedData['unidade_organica'] = $userData['unidade_organica'];
        }

        return !empty($extractedData) ? $extractedData : null;
    }

    /**
     * Converter formato de data da UCM (18-9-1997) para formato MySQL (1997-09-18)
     *
     * @param string $ucmDate
     * @return string|null
     */
    private function convertUCMDateFormat(string $ucmDate): ?string
    {
        try {
            // Formato UCM: "18-9-1997" ou "18-09-1997"
            $parts = explode('-', $ucmDate);
            
            if (count($parts) !== 3) {
                return null;
            }
            
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $year = $parts[2];
            
            // Validar data
            if (!checkdate((int)$month, (int)$day, (int)$year)) {
                return null;
            }
            
            // Retornar no formato MySQL: YYYY-MM-DD
            return $year . '-' . $month . '-' . $day;
            
        } catch (\Exception $e) {
            Log::warning('Failed to convert UCM date format', [
                'original_date' => $ucmDate,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Atualizar o perfil com os dados externos
     *
     * @param UserProfile $userProfile
     * @param array $externalData
     * @return bool
     */
    private function updateProfileWithExternalData(UserProfile $userProfile, array $externalData): bool
    {
        try {
            $updatedFields = [];
            
            // Mapear dados externos para campos do UserProfile (apenas campos existentes na tabela)
            $fieldMapping = [
                'phone' => 'phone',
                'address' => 'address', 
                'date_of_birth' => 'date_of_birth',
                'city' => 'city',
                'country' => 'country',
                'gender' => 'gender',
                'bio' => 'bio',
                'student_code' => 'student_code',
                'full_name' => 'full_name',
                'father_name' => 'father_name',
                'mother_name' => 'mother_name',
                'faculdade' => 'faculdade',
                'unidade_organica' => 'unidade_organica'
            ];

            foreach ($fieldMapping as $externalField => $profileField) {
                if (isset($externalData[$externalField]) && !empty($externalData[$externalField])) {
                    // Só atualizar se o campo atual estiver vazio
                    if (empty($userProfile->{$profileField})) {
                        $userProfile->{$profileField} = $externalData[$externalField];
                        $updatedFields[] = $profileField;
                    }
                }
            }

            // Salvar apenas se houve alterações
            if (!empty($updatedFields)) {
                $userProfile->save();
                
                Log::info('UserProfile enriched successfully', [
                    'user_profile_id' => $userProfile->id,
                    'user_name' => $userProfile->user->name,
                    'updated_fields' => $updatedFields,
                    'fields_count' => count($updatedFields)
                ]);
                
                return true;
            }

            Log::info('UserProfile enrichment: No fields updated (all fields already have data)', [
                'user_profile_id' => $userProfile->id,
                'user_name' => $userProfile->user->name
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error('UserProfile update failed during enrichment', [
                'user_profile_id' => $userProfile->id,
                'error' => $e->getMessage(),
                'external_data' => $externalData
            ]);
            
            return false;
        }
    }

    /**
     * Verificar se o enriquecimento está habilitado
     *
     * @return bool
     */
    public function isEnrichmentEnabled(): bool
    {
        return config('services.user_enrichment.enabled', false);
    }

    /**
     * Enriquecer perfil manualmente (pode ser usado via comando/job)
     *
     * @param int $userProfileId
     * @return bool
     */
    public function enrichProfileById(int $userProfileId): bool
    {
        $userProfile = UserProfile::with('user')->find($userProfileId);
        
        if (!$userProfile) {
            Log::warning('UserProfile not found for enrichment', ['id' => $userProfileId]);
            return false;
        }

        return $this->enrichProfile($userProfile);
    }
}