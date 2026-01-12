<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Publication;
use App\Models\University\University;
use App\Models\Student\Student;
use App\Models\Library\Book;
use App\Models\Documents\Document;
use App\Models\Student\StudentFinancialRecord;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Buscar métricas do dashboard
     */
    public function getMetrics(): array
    {
        $users = User::count();
        $publications = Publication::count();
        $universities = University::count();
        $students = StudentFinancialRecord::count();
        $libraryBooks = Book::count();
        $documents = Document::count();

        return [
                    'users' => $users,
                    'publications' => $publications,
                    'universities' => $universities,
                    'students' => $students,
                    'books' => $libraryBooks,
                    'documents' => $documents,
                ];
        // return [
        //     'users' => $this->getUsersMetrics(),
        //     'publications' => $this->getPublicationsMetrics(),
        //     'universities' => $this->getUniversitiesMetrics(),
        //     'students' => $this->getStudentsMetrics(),
        //     'library' => $this->getLibraryMetrics(),
        //     'documents' => $this->getDocumentsMetrics(),
        //     'system' => $this->getSystemMetrics(),
        // ];
    }

    /**
     * Métricas dos usuários
     */
    private function getUsersMetrics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::whereNull('deleted_at')->count();
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
                                  ->whereYear('created_at', Carbon::now()->year)
                                  ->count();

        // Usuários por role usando Eloquent
        $usersByRole = [];
        try {
            $rolesModel = app('Spatie\Permission\Models\Role');
            $roles = $rolesModel::withCount('users')->get();
            foreach ($roles as $role) {
                $usersByRole[$role->name] = $role->users_count;
            }
        } catch (\Exception $e) {
            // Se o modelo de roles não existir, retorna array vazio
            $usersByRole = [];
        }

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $totalUsers - $activeUsers,
            'new_this_month' => $newUsersThisMonth,
            'by_role' => $usersByRole,
        ];
    }

    /**
     * Métricas das publicações
     */
    private function getPublicationsMetrics(): array
    {
        $totalPublications = Publication::count();
        $activePublications = Publication::where('expires_at', '>', Carbon::now())
                                        ->orWhereNull('expires_at')
                                        ->count();
        $expiredPublications = Publication::where('expires_at', '<=', Carbon::now())->count();
        
        $publicationsThisMonth = Publication::whereMonth('created_at', Carbon::now()->month)
                                           ->whereYear('created_at', Carbon::now()->year)
                                           ->count();

        // Publicações por universidade usando Eloquent
        $publicationsByUniversity = Publication::whereNotNull('university_name')
                                              ->select('university_name')
                                              ->selectRaw('count(*) as total')
                                              ->groupBy('university_name')
                                              ->orderByDesc('total')
                                              ->limit(5)
                                              ->get()
                                              ->pluck('total', 'university_name')
                                              ->toArray();

        return [
            'total' => $totalPublications,
            'active' => $activePublications,
            'expired' => $expiredPublications,
            'new_this_month' => $publicationsThisMonth,
            'by_university' => $publicationsByUniversity,
        ];
    }

    /**
     * Métricas das universidades
     */
    private function getUniversitiesMetrics(): array
    {
        $totalUniversities = 0;
        $activeUniversities = 0;

        // Verificar se o modelo University existe
        if (class_exists('App\Models\University\University')) {
            $totalUniversities = University::count();
            $activeUniversities = University::whereNull('deleted_at')->count();
        }

        return [
            'total' => $totalUniversities,
            'active' => $activeUniversities,
        ];
    }

    /**
     * Métricas dos estudantes
     */
    private function getStudentsMetrics(): array
    {
        $totalStudents = 0;
        $activeStudents = 0;
        $newStudentsThisMonth = 0;

        // Verificar se o modelo Student existe
        if (class_exists('App\Models\Student\Student')) {
            $totalStudents = Student::count();
            $activeStudents = Student::whereNull('deleted_at')->count();
            $newStudentsThisMonth = Student::whereMonth('created_at', Carbon::now()->month)
                                          ->whereYear('created_at', Carbon::now()->year)
                                          ->count();
        }

        return [
            'total' => $totalStudents,
            'active' => $activeStudents,
            'new_this_month' => $newStudentsThisMonth,
        ];
    }

    /**
     * Métricas da biblioteca
     */
    private function getLibraryMetrics(): array
    {
        $totalBooks = 0;
        $availableBooks = 0;

        // Verificar se o modelo Book existe
        if (class_exists('App\Models\Library\Book')) {
            $totalBooks = Book::count();
            $availableBooks = Book::where('status', 'available')->count();
        }

        return [
            'total_books' => $totalBooks,
            'available_books' => $availableBooks,
        ];
    }

    /**
     * Métricas dos documentos
     */
    private function getDocumentsMetrics(): array
    {
        $totalDocuments = 0;
        $documentsThisMonth = 0;

        // Verificar se o modelo Document existe
        if (class_exists('App\Models\Documents\Document')) {
            $totalDocuments = Document::count();
            $documentsThisMonth = Document::whereMonth('created_at', Carbon::now()->month)
                                         ->whereYear('created_at', Carbon::now()->year)
                                         ->count();
        }

        return [
            'total' => $totalDocuments,
            'new_this_month' => $documentsThisMonth,
        ];
    }

    /**
     * Métricas do sistema
     */
    private function getSystemMetrics(): array
    {
        // Dados de crescimento mensal dos últimos 6 meses
        $monthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M/Y');
            
            $monthlyGrowth[] = [
                'month' => $monthName,
                'users' => User::whereYear('created_at', $date->year)
                              ->whereMonth('created_at', $date->month)
                              ->count(),
                'publications' => Publication::whereYear('created_at', $date->year)
                                            ->whereMonth('created_at', $date->month)
                                            ->count(),
            ];
        }

        return [
            'monthly_growth' => $monthlyGrowth,
            'last_updated' => Carbon::now()->toISOString(),
            'total_records' => $this->getTotalRecords(),
        ];
    }

    /**
     * Obter total de registos principais usando Eloquent
     */
    private function getTotalRecords(): int
    {
        try {
            $totalRecords = 0;
            
            // Contar registos das principais entidades
            $totalRecords += User::count();
            $totalRecords += Publication::count();
            
            // Verificar se UserProfile existe
            if (class_exists('App\Models\UserProfile')) {
                $totalRecords += UserProfile::count();
            }
            
            // Verificar se outros modelos existem
            if (class_exists('App\Models\University\University')) {
                $totalRecords += University::count();
            }
            
            if (class_exists('App\Models\Student\Student')) {
                $totalRecords += Student::count();
            }
            
            if (class_exists('App\Models\Library\Book')) {
                $totalRecords += Book::count();
            }
            
            if (class_exists('App\Models\Documents\Document')) {
                $totalRecords += Document::count();
            }
            
            return $totalRecords;
        } catch (\Exception $e) {
            return 0;
        }
    }
}