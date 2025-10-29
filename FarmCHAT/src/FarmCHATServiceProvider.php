<?php

namespace FarmCHAT;

use FarmCHAT\Console\InstallCommand;
use FarmCHAT\Console\PublishCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FarmCHATServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('FarmCHATMessenger', function () {
            return new \FarmCHAT\FarmCHATMessenger;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Views and Routes
        $this->loadViewsFrom(__DIR__ . '/views', 'FarmCHAT');
        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
            ]);
            $this->setPublishes();
        }
    }

    /**
     * Publishing the files that the user may override.
     *
     * @return void
     */
    protected function setPublishes()
    {
        // Load user's avatar folder from package's config
        $userAvatarFolder = json_decode(json_encode(include(__DIR__.'/config/FarmCHAT.php')))->user_avatar->folder;

        // Config
        $this->publishes([
            __DIR__ . '/config/FarmCHAT.php' => config_path('FarmCHAT.php')
        ], 'FarmCHAT-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/database/migrations/2022_01_10_99999_add_active_status_to_users.php' => database_path('migrations/' . date('Y_m_d') . '_999999_add_active_status_to_users.php'),
            __DIR__ . '/database/migrations/2022_01_10_99999_add_avatar_to_users.php' => database_path('migrations/' . date('Y_m_d') . '_999999_add_avatar_to_users.php'),
            __DIR__ . '/database/migrations/2022_01_10_99999_add_dark_mode_to_users.php' => database_path('migrations/' . date('Y_m_d') . '_999999_add_dark_mode_to_users.php'),
            __DIR__ . '/database/migrations/2022_01_10_99999_add_messenger_color_to_users.php' => database_path('migrations/' . date('Y_m_d') . '_999999_add_messenger_color_to_users.php'),
            __DIR__ . '/database/migrations/2022_01_10_99999_create_FarmCHAT_favorites_table.php' => database_path('migrations/' . date('Y_m_d') . '_999999_create_FarmCHAT_favorites_table.php'),
            __DIR__ . '/database/migrations/2022_01_10_99999_create_FarmCHAT_messages_table.php' => database_path('migrations/' . date('Y_m_d') . '_999999_create_FarmCHAT_messages_table.php'),
        ], 'FarmCHAT-migrations');

        // Models
        $isV8 = explode('.', app()->version())[0] >= 8;
        $this->publishes([
            __DIR__ . '/Models' => app_path($isV8 ? 'Models' : '')
        ], 'FarmCHAT-models');

        // Controllers
        $this->publishes([
            __DIR__ . '/Http/Controllers' => app_path('Http/Controllers/vendor/FarmCHAT')
        ], 'FarmCHAT-controllers');

        // Views
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/FarmCHAT')
        ], 'FarmCHAT-views');

        // Assets
        $this->publishes([
            // CSS
            __DIR__ . '/assets/css' => public_path('css/FarmCHAT'),
            // JavaScript
            __DIR__ . '/assets/js' => public_path('js/FarmCHAT'),
            // Images
            __DIR__ . '/assets/imgs' => storage_path('app/public/' . $userAvatarFolder),
             // CSS
             __DIR__ . '/assets/sounds' => public_path('sounds/FarmCHAT'),
        ], 'FarmCHAT-assets');

        // Routes (API and Web)
        $this->publishes([
            __DIR__ . '/routes' => base_path('routes/FarmCHAT')
        ], 'FarmCHAT-routes');
    }

    /**
     * Group the routes and set up configurations to load them.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        if (config('FarmCHAT.routes.custom')) {
            Route::group($this->routesConfigurations(), function () {
                $this->loadRoutesFrom(base_path('routes/FarmCHAT/web.php'));
            });
            Route::group($this->apiRoutesConfigurations(), function () {
                $this->loadRoutesFrom(base_path('routes/FarmCHAT/api.php'));
            });
        } else {
            Route::group($this->routesConfigurations(), function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
            Route::group($this->apiRoutesConfigurations(), function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
            });
        }
    }

    /**
     * Routes configurations.
     *
     * @return array
     */
    private function routesConfigurations()
    {
        return [
            'prefix' => config('FarmCHAT.routes.prefix'),
            'namespace' =>  config('FarmCHAT.routes.namespace'),
            'middleware' => config('FarmCHAT.routes.middleware'),
        ];
    }
    /**
     * API routes configurations.
     *
     * @return array
     */
    private function apiRoutesConfigurations()
    {
        return [
            'prefix' => config('FarmCHAT.api_routes.prefix'),
            'namespace' =>  config('FarmCHAT.api_routes.namespace'),
            'middleware' => config('FarmCHAT.api_routes.middleware'),
        ];
    }
}
