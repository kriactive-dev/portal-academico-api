<?php

namespace App\Services\EnvironmentVariable;

use App\Models\EnvironmentVariable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EnvironmentVariableService
{
    /**
     * Lista todas as variáveis de ambiente com paginação
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EnvironmentVariable::query();

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        // Filtro por status ativo
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        // Filtro por busca na chave ou descrição
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('key', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('key')->paginate($perPage);
    }

    /**
     * Busca variável por ID
     */
    public function getById(int $id): ?EnvironmentVariable
    {
        return EnvironmentVariable::find($id);
    }

    public function getSituacaoAcademica(int $id)
    {
        try {
            // Busca a chave da UCM nas variáveis de ambiente
            $chave = $this->getValue('UCM_API_KEY', '0F5DD14AE2E38C7EBD8814D29CF6F6F0'); 
            $codigoEstudante = $id;
            $hash = md5($chave . $codigoEstudante);

            // URL do endpoint da UCM
            $url = "https://esura.ucm.ac.mz/eSURA/campusOnline/getFullResultsByStudentCode/{$codigoEstudante}/{$hash}";
            
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
                'codigo_estudante' => $id,
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
    
    public function getSituacaoFinanceira(int $id)
    {
        try {
            // Busca a chave da UCM nas variáveis de ambiente
            $chave = $this->getValue('UCM_API_KEY', '0F5DD14AE2E38C7EBD8814D29CF6F6F0'); 
            $codigoEstudante = $id;
            $hash = md5($chave . $codigoEstudante);

            // URL do endpoint da UCM
            $url = "https://primaveraapi.ucm.ac.mz/api/extracto/{$codigoEstudante}/{$hash}";
            
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
                'codigo_estudante' => $id,
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
    
    /**
     * Busca variável por chave
     */
    public function getByKey(string $key): ?EnvironmentVariable
    {
        return EnvironmentVariable::byKey($key)->first();
    }

    /**
     * Busca valor de uma variável por chave (descriptografado se necessário)
     */
    public function getValue(string $key, $default = null)
    {
        return EnvironmentVariable::getValue($key, $default);
    }

    /**
     * Cria uma nova variável de ambiente
     */
    public function create(array $data): EnvironmentVariable
    {
        // Verifica se a chave já existe
        if (EnvironmentVariable::byKey($data['key'])->exists()) {
            throw new Exception('Já existe uma variável com esta chave.');
        }

        return EnvironmentVariable::create($data);
    }

    /**
     * Atualiza uma variável de ambiente
     */
    public function update(int $id, array $data): EnvironmentVariable
    {
        $variable = $this->getById($id);
        
        if (!$variable) {
            throw new Exception('Variável de ambiente não encontrada.');
        }

        // Verifica se está tentando alterar a chave para uma que já existe
        if (isset($data['key']) && $data['key'] !== $variable->key) {
            if (EnvironmentVariable::byKey($data['key'])->exists()) {
                throw new Exception('Já existe uma variável com esta chave.');
            }
        }

        $variable->update($data);
        
        return $variable->fresh();
    }

    /**
     * Define ou atualiza uma variável (upsert)
     */
    public function setValue(string $key, $value, string $description = null, string $category = null, bool $isEncrypted = false): EnvironmentVariable
    {
        return EnvironmentVariable::setValue($key, $value, $description, $category, $isEncrypted);
    }

    /**
     * Remove uma variável de ambiente
     */
    public function delete(int $id): bool
    {
        $variable = $this->getById($id);
        
        if (!$variable) {
            throw new Exception('Variável de ambiente não encontrada.');
        }

        return $variable->delete();
    }

    /**
     * Ativa/desativa uma variável
     */
    public function toggleActive(int $id): EnvironmentVariable
    {
        $variable = $this->getById($id);
        
        if (!$variable) {
            throw new Exception('Variável de ambiente não encontrada.');
        }

        $variable->update(['is_active' => !$variable->is_active]);
        
        return $variable->fresh();
    }

    /**
     * Busca variáveis por categoria
     */
    public function getByCategory(string $category): Collection
    {
        return EnvironmentVariable::active()->byCategory($category)->get();
    }

    /**
     * Lista todas as categorias disponíveis
     */
    public function getCategories(): SupportCollection
    {
        return EnvironmentVariable::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->get()
            ->pluck('category');
    }

    /**
     * Exporta variáveis como array chave => valor
     */
    public function exportAsArray(string $category = null, bool $onlyActive = true): array
    {
        $query = EnvironmentVariable::query();

        if ($onlyActive) {
            $query->active();
        }

        if ($category) {
            $query->byCategory($category);
        }

        $variables = $query->get();
        $result = [];

        foreach ($variables as $variable) {
            $result[$variable->key] = $variable->getDecryptedValueAttribute();
        }

        return $result;
    }

    /**
     * Importa variáveis de um array
     */
    public function importFromArray(array $variables, string $category = null, bool $isEncrypted = false): int
    {
        $count = 0;

        foreach ($variables as $key => $value) {
            try {
                $this->setValue($key, $value, null, $category, $isEncrypted);
                $count++;
            } catch (Exception $e) {
                // Log do erro mas continua a importação
                \Log::warning("Erro ao importar variável {$key}: " . $e->getMessage());
            }
        }

        return $count;
    }
}