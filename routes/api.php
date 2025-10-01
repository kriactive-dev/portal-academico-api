<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Library\BookController;
use App\Http\Controllers\Api\Library\LibraryController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\RolePermission\RolePermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Google OAuth Routes
    Route::get('google', [GoogleAuthController::class, 'redirect']);
    Route::get('google/callback', [GoogleAuthController::class, 'callback']);
    Route::get('google/callback-json', [GoogleAuthController::class, 'callbackJson']);
    Route::get('google/user-info', [GoogleAuthController::class, 'getUserInfo']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
    
    // Google OAuth - Rotas protegidas
    Route::post('google/unlink', [GoogleAuthController::class, 'unlink']);
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {

    Route::get('/', [UserController::class, 'index']);                   
    Route::post('/', [UserController::class, 'store']);                   
    Route::get('/stats', [UserController::class, 'stats']);              
    Route::get('/search', [UserController::class, 'search']);            
    Route::get('/{id}', [UserController::class, 'show']);                
    Route::put('/{id}', [UserController::class, 'update']);             
    Route::delete('/{id}', [UserController::class, 'destroy']);    

    // Ações especiais
    Route::patch('/{id}/restore', [UserController::class, 'restore']);    
    Route::delete('/{id}/force', [UserController::class, 'forceDelete']); 
    Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus']); 
    Route::patch('/{id}/reset-password', [UserController::class, 'resetPassword']); 
    Route::patch('/{id}/verify-email', [UserController::class, 'verifyEmail']);
});

// Rotas de gerenciamento de livros (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('books')->group(function () {
    // CRUD básico
    Route::get('/', [BookController::class, 'index']);                    
    Route::post('/', [BookController::class, 'store']);                   
    Route::get('/stats', [BookController::class, 'stats']);             
    Route::get('/search', [BookController::class, 'search']);           
    Route::get('/{id}', [BookController::class, 'show']);                
    Route::put('/{id}', [BookController::class, 'update']);            
    Route::delete('/{id}', [BookController::class, 'destroy']);       
    
    // Ações especiais
    Route::patch('/{id}/restore', [BookController::class, 'restore']);   
    Route::delete('/{id}/force', [BookController::class, 'forceDelete']); 
    Route::post('/{id}/duplicate', [BookController::class, 'duplicate']); 
    Route::get('/{id}/download', [BookController::class, 'downloadFile']); 
    
    // Busca por biblioteca
    Route::get('/library/{libraryId}', [BookController::class, 'getByLibrary']);
});

// Rotas de gerenciamento de bibliotecas (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('libraries')->group(function () {
    // CRUD básico
    Route::get('/', [LibraryController::class, 'index']);                   
    Route::post('/', [LibraryController::class, 'store']);                  
    Route::get('/stats', [LibraryController::class, 'stats']);            
    Route::get('/search', [LibraryController::class, 'search']);           
    Route::get('/{id}', [LibraryController::class, 'show']);               
    Route::put('/{id}', [LibraryController::class, 'update']);            
    Route::delete('/{id}', [LibraryController::class, 'destroy']);        
    
    // Ações especiais
    Route::patch('/{id}/restore', [LibraryController::class, 'restore']);  
    Route::delete('/{id}/force', [LibraryController::class, 'forceDelete']); 
    
    // Gerenciamento de livros
    Route::get('/{id}/books', [LibraryController::class, 'getBooks']);     
    Route::post('/{id}/books/transfer', [LibraryController::class, 'transferBooks']); 
    Route::get('/{id}/books/count', [LibraryController::class, 'getBooksCount']); 
    
    // Estatísticas específicas
    Route::get('/{id}/stats', [LibraryController::class, 'getLibraryStats']); 
});

// Rotas de gerenciamento de roles e permissions (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('roles-permissions')->group(function () {
    // ===== GERENCIAMENTO DE ROLES =====
    Route::prefix('roles')->group(function () {
        Route::get('/', [RolePermissionController::class, 'indexRoles']);                   
        Route::post('/', [RolePermissionController::class, 'storeRole']);                  
        Route::get('/search', [RolePermissionController::class, 'searchRoles']);           
        Route::get('/available', [RolePermissionController::class, 'getAvailableRoles']); 
        Route::get('/{id}', [RolePermissionController::class, 'showRole']);                
        Route::put('/{id}', [RolePermissionController::class, 'updateRole']);             
        Route::delete('/{id}', [RolePermissionController::class, 'destroyRole']);         
        Route::post('/{id}/duplicate', [RolePermissionController::class, 'duplicateRole']); 
    });

    // ===== GERENCIAMENTO DE PERMISSIONS =====
    Route::prefix('permissions')->group(function () {
        Route::get('/', [RolePermissionController::class, 'indexPermissions']);                   
        Route::post('/', [RolePermissionController::class, 'storePermission']);                  
        Route::get('/search', [RolePermissionController::class, 'searchPermissions']);           
        Route::get('/available', [RolePermissionController::class, 'getAvailablePermissions']); 
        Route::get('/{id}', [RolePermissionController::class, 'showPermission']);                
        Route::put('/{id}', [RolePermissionController::class, 'updatePermission']);             
        Route::delete('/{id}', [RolePermissionController::class, 'destroyPermission']);         
    });

    // ===== ATRIBUIÇÃO DE ROLES E PERMISSIONS =====
    Route::prefix('assignments')->group(function () {
        // Roles
        Route::post('/roles/assign', [RolePermissionController::class, 'assignRoleToUser']);     
        Route::post('/roles/remove', [RolePermissionController::class, 'removeRoleFromUser']);   
        Route::post('/roles/sync', [RolePermissionController::class, 'syncUserRoles']);          
        
        // Permissions
        Route::post('/permissions/assign', [RolePermissionController::class, 'assignPermissionToUser']); 
        Route::post('/permissions/remove', [RolePermissionController::class, 'removePermissionFromUser']); 
    });

    // ===== CONSULTAS E VERIFICAÇÕES =====
    Route::prefix('check')->group(function () {
        Route::post('/user-permission', [RolePermissionController::class, 'checkUserPermission']); 
        Route::post('/user-role', [RolePermissionController::class, 'checkUserRole']);             
        Route::get('/user/{userId}/permissions', [RolePermissionController::class, 'getUserPermissions']); 
    });

    // ===== ESTATÍSTICAS =====
    Route::get('/stats', [RolePermissionController::class, 'getStats']);                        
});