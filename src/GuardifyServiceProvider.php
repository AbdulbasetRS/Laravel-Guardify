<?php

namespace Abdulbaset\Guardify;

use Illuminate\Support\ServiceProvider;
use Abdulbaset\Guardify\Providers\BladeServiceProvider;

/**
 * GuardifyServiceProvider
 *
 * The main service provider for the Laravel Guardify package.
 * This class handles package registration, configuration publishing, and service bootstrapping.
 * It's responsible for setting up the package's core functionality and making it available
 * to the Laravel application.
 *
 * @package Abdulbaset\Guardify
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class GuardifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the main class to use with the facade
        $this->mergeConfigFrom(
            __DIR__.'/../config/guardify.php', 'guardify'
        );

        // Register the Blade service provider
        $this->app->register(BladeServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/guardify.php' => config_path('guardify.php'),
        ], 'guardify-config');

        // Load migrations directly from the package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Optionally allow publishing migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'guardify-migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\RolesSeedCommand::class,
                Console\Commands\RolesSyncCommand::class,
                Console\Commands\PermissionsSeedCommand::class,
                Console\Commands\PermissionsSyncCommand::class,
            ]);
        }
        
        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('role', \Abdulbaset\Guardify\Http\Middleware\RoleMiddleware::class);
        $router->aliasMiddleware('permission', \Abdulbaset\Guardify\Http\Middleware\PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', \Abdulbaset\Guardify\Http\Middleware\RoleOrPermissionMiddleware::class);
    }
}
