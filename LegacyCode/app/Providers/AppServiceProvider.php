<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\Services\Library\BookService;
use App\Services\Library\LibraryService;
use App\Services\RolePermission\RolePermissionService;
use App\Services\OAuth\GoogleAuthService;
use App\Services\User\UserProfileEnrichmentService;
use App\Models\UserProfile;
use App\Observers\UserProfileObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthService::class, function () {
            return new AuthService();
        });

        $this->app->bind(UserService::class, function () {
            return new UserService();
        });

        $this->app->bind(BookService::class, function () {
            return new BookService();
        });

        $this->app->bind(LibraryService::class, function () {
            return new LibraryService();
        });

        $this->app->bind(RolePermissionService::class, function () {
            return new RolePermissionService();
        });

        $this->app->bind(GoogleAuthService::class, function () {
            return new GoogleAuthService();
        });

        $this->app->bind(UserProfileEnrichmentService::class, function () {
            return new UserProfileEnrichmentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observer para UserProfile
        UserProfile::observe(UserProfileObserver::class);
    }
}
