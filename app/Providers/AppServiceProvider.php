<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\CompanyRepository;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 *
 * Registers application services and binds interfaces to implementations.
 *
 * Pattern: Dependency Injection Container
 * Laravel's service container manages class dependencies and performs
 * dependency injection. This provider configures those bindings.
 *
 * Benefits:
 * - Loose coupling: Classes depend on interfaces, not implementations
 * - Testability: Easily swap implementations for testing
 * - Flexibility: Change implementations without modifying client code
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is called before boot() and is used for binding
     * services into the container.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        // This enables dependency injection throughout the application
        $this->app->bind(
            CompanyRepositoryInterface::class,
            CompanyRepository::class
        );

        // The RegistryProviderFactory doesn't need binding
        // as it's a concrete class with constructor injection
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all services are registered.
     * Use for any bootstrapping logic.
     */
    public function boot(): void
    {
        //
    }
}
