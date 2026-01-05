<?php

namespace App\Http\Controllers\Api\EnvironmentVariable;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentVariable\EnvironmentVariableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class EnvironmentVariableController extends Controller
{
    protected EnvironmentVariableService $environmentVariableService;

    public function __construct(EnvironmentVariableService $environmentVariableService)
    {
        $this->environmentVariableService = $environmentVariableService;
    }

    /**
     * Listar todas as variáveis de ambiente com paginação
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $filters = [
                'category' => $request->get('category'),
                'is_active' => $request->get('is_active'),
                'search' => $request->get('search'),
            ];

            $variables = $this->environmentVariableService->getAll($filters, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Variáveis de ambiente listadas com sucesso.',
                'data' => $variables->items(),
                'meta' => [
                    'current_page' => $variables->currentPage(),
                    'last_page' => $variables->lastPage(),
                    'per_page' => $variables->perPage(),
                    'total' => $variables->total(),
                    'from' => $variables->firstItem(),
                    'to' => $variables->lastItem(),
                ],
                'links' => [
                    'first' => $variables->url(1),
                    'last' => $variables->url($variables->lastPage()),
                    'prev' => $variables->previousPageUrl(),
                    'next' => $variables->nextPageUrl(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar variáveis de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas as categorias disponíveis
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = $this->environmentVariableService->getCategories();

            return response()->json([
                'success' => true,
                'message' => 'Categorias listadas com sucesso.',
                'data' => $categories,
                'total' => $categories->count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar categorias.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar variáveis por categoria
     *
     * @param Request $request
     * @param string $category
     * @return JsonResponse
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        try {
            $variables = $this->environmentVariableService->getByCategory($category);

            return response()->json([
                'success' => true,
                'message' => "Variáveis da categoria '{$category}' listadas com sucesso.",
                'data' => $variables,
                'total' => $variables->count()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar variáveis por categoria.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Armazenar uma nova variável de ambiente
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|unique:environment_variables,key',
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_encrypted' => 'boolean',
        ], [
            'key.required' => 'A chave é obrigatória.',
            'key.unique' => 'Já existe uma variável com esta chave.',
            'value.required' => 'O valor é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $variable = $this->environmentVariableService->create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Variável de ambiente criada com sucesso.',
                'data' => $variable
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar variável de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar uma variável de ambiente específica
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $variable = $this->environmentVariableService->getById($id);

            if (!$variable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variável de ambiente não encontrada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variável de ambiente encontrada.',
                'data' => $variable
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar variável de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar variável por chave
     *
     * @param string $key
     * @return JsonResponse
     */
    public function getByKey(string $key): JsonResponse
    {
        try {
            $variable = $this->environmentVariableService->getByKey($key);

            if (!$variable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variável de ambiente não encontrada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variável de ambiente encontrada.',
                'data' => $variable
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar variável de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar valor de uma variável por chave (descriptografado)
     *
     * @param string $key
     * @return JsonResponse
     */
    public function getValue(string $key): JsonResponse
    {
        try {
            $value = $this->environmentVariableService->getValue($key);

            if ($value === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variável de ambiente não encontrada.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Valor da variável encontrado.',
                'data' => [
                    'key' => $key,
                    'value' => $value
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar valor da variável.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar uma variável de ambiente
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:255|unique:environment_variables,key,' . $id,
            'value' => 'sometimes|string',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
        ], [
            'key.unique' => 'Já existe uma variável com esta chave.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $variable = $this->environmentVariableService->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Variável de ambiente atualizada com sucesso.',
                'data' => $variable
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar variável de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/desativar uma variável de ambiente
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $variable = $this->environmentVariableService->toggleActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Status da variável alterado com sucesso.',
                'data' => $variable
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status da variável.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Definir ou atualizar uma variável (upsert)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setValue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_encrypted' => 'boolean',
        ], [
            'key.required' => 'A chave é obrigatória.',
            'value.required' => 'O valor é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $variable = $this->environmentVariableService->setValue(
                $request->key,
                $request->value,
                $request->description,
                $request->category,
                $request->boolean('is_encrypted', false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Variável definida com sucesso.',
                'data' => $variable
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao definir variável.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover uma variável de ambiente
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->environmentVariableService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Variável de ambiente removida com sucesso.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover variável de ambiente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar variáveis como array
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $category = $request->get('category');
            $onlyActive = $request->boolean('only_active', true);

            $variables = $this->environmentVariableService->exportAsArray($category, $onlyActive);

            return response()->json([
                'success' => true,
                'message' => 'Variáveis exportadas com sucesso.',
                'data' => $variables,
                'total' => count($variables)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar variáveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar variáveis de um array
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'variables' => 'required|array',
            'category' => 'nullable|string|max:100',
            'is_encrypted' => 'boolean',
        ], [
            'variables.required' => 'O array de variáveis é obrigatório.',
            'variables.array' => 'As variáveis devem ser fornecidas como um array.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = $this->environmentVariableService->importFromArray(
                $request->variables,
                $request->category,
                $request->boolean('is_encrypted', false)
            );

            return response()->json([
                'success' => true,
                'message' => "Importação concluída. {$count} variáveis importadas.",
                'data' => [
                    'imported_count' => $count,
                    'total_submitted' => count($request->variables)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar variáveis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showSituacaoAcademica(string $id): JsonResponse
    {
        try {
            $result = $this->environmentVariableService->getSituacaoAcademica($id);

            // O service agora retorna um array com success, data e message
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar situação acadêmica.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showSituacaoFinanceira(string $id): JsonResponse
    {
        try {
            $result = $this->environmentVariableService->getSituacaoFinanceira($id);

            // O service agora retorna um array com success, data e message
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error' => $result['error'] ?? null
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar situação acadêmica.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}