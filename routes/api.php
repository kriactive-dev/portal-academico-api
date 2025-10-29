<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\FirebaseAuthController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\ChatBot\ChatBotController;
use App\Http\Controllers\Api\ChatBot\OptionController;
use App\Http\Controllers\Api\ChatBot\QuestionController;
use App\Http\Controllers\Api\ChatBot\WebChatBotController;
use App\Http\Controllers\Api\ExternalApp\ExternalAppController;
use App\Http\Controllers\Api\Library\BookController;
use App\Http\Controllers\Api\Library\LibraryController;
use App\Http\Controllers\Api\Publication\PublicationController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\RolePermission\RolePermissionController;
use App\Http\Controllers\Api\University\CourseController;
use App\Http\Controllers\Api\Documents\DocumentTypeController;
use App\Http\Controllers\Api\Library\BookCategoryController;
use App\Http\Controllers\Api\Notification\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('notifications')->group(function () {
    Route::post('push', [NotificationController::class, 'send']);
    
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Google OAuth Routes
    Route::get('google', [GoogleAuthController::class, 'redirect']);
    Route::get('google/callback', [GoogleAuthController::class, 'callback']);
    Route::get('google/callback-json', [GoogleAuthController::class, 'callbackJson']);
    Route::get('google/user-info', [GoogleAuthController::class, 'getUserInfo']);
    
    // Firebase Auth Routes (Públicas)
    Route::post('firebase/login', [FirebaseAuthController::class, 'loginWithFirebase']);
    Route::post('firebase/verify-token', [FirebaseAuthController::class, 'verifyToken']);
    Route::get('firebase/check-email', [FirebaseAuthController::class, 'checkEmail']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
    
    // Google OAuth - Rotas protegidas
    Route::post('google/unlink', [GoogleAuthController::class, 'unlink']);
    
    // Firebase Auth - Rotas protegidas
    Route::post('firebase/logout', [FirebaseAuthController::class, 'logout']);
    Route::post('firebase/revoke-all-tokens', [FirebaseAuthController::class, 'revokeAllTokens']);
    Route::get('firebase/user-info', [FirebaseAuthController::class, 'getUserInfo']);
    Route::post('firebase/sync-user', [FirebaseAuthController::class, 'syncUser']);

    Route::post('firebase/register-device-token', [FirebaseAuthController::class, 'savetoken']);
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
    Route::get('/categories', [BookController::class, 'indexCategories']);             
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

// Rotas de gerenciamento de publicações (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('publications')->group(function () {
    Route::get('/universities', [PublicationController::class, 'indexUniversity']);      

    // CRUD básico
    Route::get('/', [PublicationController::class, 'index']);                    
    Route::post('/', [PublicationController::class, 'store']);                  
    Route::get('/stats', [PublicationController::class, 'stats']);              
    Route::get('/search', [PublicationController::class, 'search']);            
    Route::get('/{id}', [PublicationController::class, 'show']);                
    Route::put('/{id}', [PublicationController::class, 'update']);             
    Route::delete('/{id}', [PublicationController::class, 'destroy']);  
    
    // Ações especiais
    Route::patch('/{id}/restore', [PublicationController::class, 'restore']);   
    Route::delete('/{id}/force', [PublicationController::class, 'forceDelete']); 
    Route::post('/{id}/duplicate', [PublicationController::class, 'duplicate']); 
    
    // Gerenciamento de arquivos
    Route::post('/{id}/upload', [PublicationController::class, 'uploadFile']);  
    Route::delete('/{id}/file', [PublicationController::class, 'removeFile']); 
    Route::get('/{id}/download', [PublicationController::class, 'downloadFile']); 
    
    // Gerenciamento de notificações
    Route::get('/{id}/notification-stats', [PublicationController::class, 'getNotificationStats']); 
    Route::post('/{id}/test-notification', [PublicationController::class, 'testNotification']); 
    Route::post('/{id}/resend-notifications', [PublicationController::class, 'resendNotifications']); 
    
    // Consultas por status
    Route::get('/status/{status}', [PublicationController::class, 'getByStatus']); 
});

// Rotas de gerenciamento de documentos (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('documents')->group(function () {
    // CRUD básico
    Route::get('/', [App\Http\Controllers\Api\Document\DocumentController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\Document\DocumentController::class, 'store']);
    Route::get('/stats', [App\Http\Controllers\Api\Document\DocumentController::class, 'stats']);
    Route::get('/types', [App\Http\Controllers\Api\Document\DocumentController::class, 'indexTypes']);
    Route::get('/search', [App\Http\Controllers\Api\Document\DocumentController::class, 'search']);
    Route::get('/{id}', [App\Http\Controllers\Api\Document\DocumentController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\Api\Document\DocumentController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\Document\DocumentController::class, 'destroy']);
    
    // Ações especiais
    Route::patch('/{id}/restore', [App\Http\Controllers\Api\Document\DocumentController::class, 'restore']);
    Route::delete('/{id}/force', [App\Http\Controllers\Api\Document\DocumentController::class, 'forceDelete']);
    Route::patch('/{id}/change-status', [App\Http\Controllers\Api\Document\DocumentController::class, 'changeStatus']);
    
    // Gerenciamento de arquivos
    Route::post('/{id}/files', [App\Http\Controllers\Api\Document\DocumentController::class, 'uploadFiles']);
    Route::delete('/{id}/files/{fileId}', [App\Http\Controllers\Api\Document\DocumentController::class, 'deleteFile']);
    Route::get('/{id}/files/{fileId}/download', [App\Http\Controllers\Api\Document\DocumentController::class, 'downloadFile']);
    
    // Consultas por status
    Route::get('/status/{statusId}', [App\Http\Controllers\Api\Document\DocumentController::class, 'getByStatus']);
});

// Rotas de gerenciamento de tipos de documento (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('document-types')->group(function () {
    // CRUD básico
    Route::get('/', [DocumentTypeController::class, 'index']);                    
    Route::post('/', [DocumentTypeController::class, 'store']);                  
    Route::get('/all', [DocumentTypeController::class, 'all']);                  
    Route::get('/stats', [DocumentTypeController::class, 'stats']);              
    Route::get('/{id}', [DocumentTypeController::class, 'show']);                
    Route::put('/{id}', [DocumentTypeController::class, 'update']);             
    Route::delete('/{id}', [DocumentTypeController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [DocumentTypeController::class, 'restore']);   
    Route::delete('/{id}/force', [DocumentTypeController::class, 'forceDelete']); 
    
    // Lixeira
    Route::get('/trashed/list', [DocumentTypeController::class, 'trashed']);     
});

// Rotas de gerenciamento de categorias de livros (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('book-categories')->group(function () {
    // CRUD básico
    Route::get('/', [BookCategoryController::class, 'index']);                    
    Route::post('/', [BookCategoryController::class, 'store']);                  
    Route::get('/all', [BookCategoryController::class, 'all']);                  
    Route::get('/with-books-count', [BookCategoryController::class, 'withBooksCount']); 
    Route::get('/most-used', [BookCategoryController::class, 'mostUsed']);       
    Route::get('/stats', [BookCategoryController::class, 'stats']);              
    Route::get('/{id}', [BookCategoryController::class, 'show']);                
    Route::put('/{id}', [BookCategoryController::class, 'update']);             
    Route::delete('/{id}', [BookCategoryController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [BookCategoryController::class, 'restore']);   
    Route::delete('/{id}/force', [BookCategoryController::class, 'forceDelete']); 
    Route::post('/{id}/duplicate', [BookCategoryController::class, 'duplicate']); 
    
    // Lixeira
    Route::get('/trashed/list', [BookCategoryController::class, 'trashed']);     
});

Route::middleware('auth:sanctum')->prefix('chatbot')->group(function () {

    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    Route::get('/options', [OptionController::class, 'index']);
    Route::get('/options/{id}', [OptionController::class, 'show']);
    Route::post('/options', [OptionController::class, 'store']);
    Route::put('/options/{id}', [OptionController::class, 'update']);
    Route::delete('/options/{id}', [OptionController::class, 'destroy']);

    // Para pegar opções de uma pergunta específica:
    Route::get('/questions/{id}/options', [OptionController::class, 'indexByQuestion']);

});

// Rotas do Web ChatBot (sem autenticação para acesso público)
Route::prefix('web-chatbot')->group(function () {
    Route::post('/init', [WebChatBotController::class, 'initChat']);
    Route::post('/message', [WebChatBotController::class, 'processMessage']);
    Route::post('/current', [WebChatBotController::class, 'getCurrentQuestion']);
    Route::post('/end', [WebChatBotController::class, 'endChat']);
    Route::post('/history', [WebChatBotController::class, 'getChatHistory']);
});

// Rotas de gerenciamento de cursos (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    // CRUD básico
    Route::get('/', [CourseController::class, 'index']);                    
    Route::post('/', [CourseController::class, 'store']);                  
    Route::get('/stats', [CourseController::class, 'stats']);              
    Route::get('/search', [CourseController::class, 'search']);            
    Route::get('/{id}', [CourseController::class, 'show']);                
    Route::put('/{id}', [CourseController::class, 'update']);             
    Route::delete('/{id}', [CourseController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [CourseController::class, 'restore']);   
    Route::delete('/{id}/force', [CourseController::class, 'forceDelete']); 
    Route::post('/{id}/duplicate', [CourseController::class, 'duplicate']); 
    
    // Consultas específicas
    Route::get('/university/{universityId}', [CourseController::class, 'getByUniversity']); 
    Route::get('/all/active', [CourseController::class, 'getAllActive']); 
});

// Rotas de gerenciamento de universidades (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('universities')->group(function () {
    // CRUD básico
    Route::get('/', [App\Http\Controllers\University\UniversityController::class, 'index']);                    
    Route::post('/', [App\Http\Controllers\University\UniversityController::class, 'store']);                  
    Route::get('/stats', [App\Http\Controllers\University\UniversityController::class, 'stats']);              
    Route::get('/search', [App\Http\Controllers\University\UniversityController::class, 'search']);            
    Route::get('/{university}', [App\Http\Controllers\University\UniversityController::class, 'show']);                
    Route::put('/{university}', [App\Http\Controllers\University\UniversityController::class, 'update']);             
    Route::delete('/{university}', [App\Http\Controllers\University\UniversityController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [App\Http\Controllers\University\UniversityController::class, 'restore']);   
    Route::delete('/{id}/force', [App\Http\Controllers\University\UniversityController::class, 'forceDestroy']); 
    Route::post('/{university}/duplicate', [App\Http\Controllers\University\UniversityController::class, 'duplicate']); 
    Route::patch('/{university}/toggle-status', [App\Http\Controllers\University\UniversityController::class, 'toggleStatus']); 
    
    // Consultas específicas
    Route::get('/all/active', [App\Http\Controllers\University\UniversityController::class, 'getAllActive']); 
    Route::get('/all/with-trashed', [App\Http\Controllers\University\UniversityController::class, 'withTrashed']); 
    
    // Operações em lote
    Route::patch('/bulk/status', [App\Http\Controllers\University\UniversityController::class, 'bulkUpdateStatus']); 
    Route::delete('/bulk/delete', [App\Http\Controllers\University\UniversityController::class, 'bulkDelete']); 
});

// Rotas de gerenciamento de registros financeiros de estudantes (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('student-financial-records')->group(function () {
    // CRUD básico
    Route::get('/', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'index']);                    
    Route::post('/', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'store']);                  
    Route::get('/stats', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'stats']);              
    Route::get('/search', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'search']);            
    Route::get('/{financialRecord}', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'show']);                
    Route::put('/{financialRecord}', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'update']);             
    Route::delete('/{financialRecord}', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'restore']);   
    Route::delete('/{id}/force', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'forceDestroy']); 
    Route::post('/{financialRecord}/duplicate', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'duplicate']); 
    
    // Consultas específicas por estudante
    Route::get('/student/{studentId}/records', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'getByStudent']); 
    Route::get('/student/{studentId}/summary', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'getStudentSummary']); 
    
    // Consultas com deletados
    Route::get('/all/with-trashed', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'withTrashed']); 
    
    // Operações em lote
    Route::patch('/bulk/status', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'bulkUpdateStatus']); 
    Route::delete('/bulk/delete', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'bulkDelete']); 
    
    // Importação Excel
    Route::post('/import/excel', [App\Http\Controllers\Student\StudentFinancialRecordController::class, 'importExcel']); 
});

// Rotas de gerenciamento de registros acadêmicos de estudantes (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('student-academic-records')->group(function () {
    // CRUD básico
    Route::get('/', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'index']);                    
    Route::post('/', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'store']);                  
    Route::get('/stats', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'stats']);              
    Route::get('/search', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'search']);            
    Route::get('/{academicRecord}', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'show']);                
    Route::put('/{academicRecord}', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'update']);             
    Route::delete('/{academicRecord}', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'restore']);   
    Route::delete('/{id}/force', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'forceDestroy']); 
    Route::post('/{academicRecord}/duplicate', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'duplicate']); 
    
    // Consultas específicas por estudante
    Route::get('/student/{studentId}/records', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'getByStudent']); 
    Route::get('/student/{studentId}/summary', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'getStudentSummary']); 
    Route::get('/student/{studentId}/transcript', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'getStudentTranscript']); 
    
    // Consultas com deletados
    Route::get('/all/with-trashed', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'withTrashed']); 
    
    // Operações em lote
    Route::patch('/bulk/grade', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'bulkUpdateGrade']); 
    Route::delete('/bulk/delete', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'bulkDelete']); 
    
    // Importação Excel
    Route::post('/import/excel', [App\Http\Controllers\Student\StudentAcademicRecordController::class, 'importExcel']); 
});

// Rotas de gerenciamento de cursos (protegidas por autenticação)
Route::middleware('auth:sanctum')->prefix('external-app')->group(function () {
    // CRUD básico
    Route::get('/', [ExternalAppController::class, 'index']);                    
    Route::post('/', [ExternalAppController::class, 'store']);                  
    Route::get('/stats', [ExternalAppController::class, 'stats']);              
    Route::get('/search', [ExternalAppController::class, 'search']);            
    Route::get('/{id}', [ExternalAppController::class, 'show']);                
    Route::put('/{id}', [ExternalAppController::class, 'update']);             
    Route::delete('/{id}', [ExternalAppController::class, 'destroy']);         
    
    // Ações especiais
    Route::patch('/{id}/restore', [ExternalAppController::class, 'restore']);   
    Route::delete('/{id}/force', [ExternalAppController::class, 'forceDelete']); 
    Route::post('/{id}/duplicate', [ExternalAppController::class, 'duplicate']); 
    
});

    Route::get('/webhook', [ChatBotController::class, 'getwebhook']);

    Route::post('/webhook', [ChatBotController::class, 'handleWebhook']);

    Route::get('/getallquestions', [QuestionController::class, 'getall']);

// ===== ROTAS DE TESTE (Temporárias) =====
Route::get('/test/health', function () {
    return response()->json([
        'status' => 'API funcionando',
        'timestamp' => now(),
        'laravel_version' => app()->version(),
        'database' => 'conectado'
    ]);
});

Route::get('/test/document-types/create', function () {
    try {
        $documentType = \App\Models\DocumentType::create([
            'name' => 'Documento de Teste ' . now()->format('Y-m-d H:i:s'),
            'created_by_user_id' => 1,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Documento de teste criado com sucesso',
            'data' => $documentType
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao criar documento: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/test/document-types/list', function () {
    try {
        $documentTypes = \App\Models\DocumentType::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Lista de tipos de documento',
            'data' => $documentTypes,
            'total' => $documentTypes->count()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao listar documentos: ' . $e->getMessage()
        ], 500);
    }
});
Route::get("/test/book-categories/create", function () {
    try {
        $category1 = \App\Models\Library\BookCategory::create([
            "name" => "Ficção Científica",
            "created_by_user_id" => 1,
        ]);
        
        $category2 = \App\Models\Library\BookCategory::create([
            "name" => "Romance",
            "created_by_user_id" => 1,
        ]);
        
        $category3 = \App\Models\Library\BookCategory::create([
            "name" => "Terror",
            "created_by_user_id" => 1,
        ]);
        
        return response()->json([
            "success" => true,
            "message" => "Categorias de teste criadas com sucesso",
            "data" => [$category1, $category2, $category3]
        ]);
    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Erro ao criar categorias: " . $e->getMessage()
        ], 500);
    }
});

Route::get("/test/book-categories/list", function () {
    try {
        $categories = \App\Models\Library\BookCategory::all();
        
        return response()->json([
            "success" => true,
            "message" => "Lista de categorias de livros",
            "data" => $categories,
            "total" => $categories->count()
        ]);
    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Erro ao listar categorias: " . $e->getMessage()
        ], 500);
    }
});
