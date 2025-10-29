<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentTypeController;

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
| Rotas de teste temporárias para verificar funcionamento da API
*/

Route::get('/test/document-types/create', function () {
    // Simular criação de um tipo de documento para teste
    $documentType = \App\Models\DocumentType::create([
        'name' => 'Documento de Teste ' . now()->format('Y-m-d H:i:s'),
        'created_by_user_id' => 1,
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Documento de teste criado com sucesso',
        'data' => $documentType
    ]);
});

Route::get('/test/document-types/list', function () {
    // Listar documentos para teste
    $documentTypes = \App\Models\DocumentType::all();
    
    return response()->json([
        'success' => true,
        'message' => 'Lista de tipos de documento',
        'data' => $documentTypes,
        'total' => $documentTypes->count()
    ]);
});

Route::get('/test/health', function () {
    return response()->json([
        'status' => 'API funcionando',
        'timestamp' => now(),
        'laravel_version' => app()->version()
    ]);
});