<?php

namespace Htop;

use Htop\Commands\HtopCommand;
use Htop\Commands\HtopInstallCommand;
use Htop\Middleware\RequestLogger;
use Htop\Storage\StorageManager;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class HtopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                HtopInstallCommand::class,
                HtopCommand::class,
            ]);
        }

        $this->app->singleton(StorageManager::class);
        $this->mergeConfigFrom(__DIR__.'/../config/htop.php', 'htop');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'htop');

        app('router')->pushMiddlewareToGroup('web', RequestLogger::class);

        $this->publishes([
            __DIR__.'/../config/websockets.php' => config_path('websockets.php'),
        ], 'htop-websockets');

        if (! class_exists('CreateWebSocketsStatisticsEntries')) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Add broadcasting routes if needed
        Broadcast::routes();

        // To publish config: php artisan vendor:publish --tag=htop-config
        // To publish views : php artisan vendor:publish --tag=htop-views
        // To publish all   : php artisan vendor:publish --tag=htop
        $this->publishes([
            __DIR__.'/../config/htop.php' => config_path('htop.php'),
        ], 'htop-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/htop'),
        ], 'htop-views');

        $this->publishes([
            base_path('vendor/laravel/reverb/config/reverb.php') => config_path('reverb.php'),
        ], 'reverb-config');

        $this->publishes([
            __DIR__.'/../config/htop.php' => config_path('htop.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/htop'),
        ], 'htop');

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(\Htop\Middleware\RequestLogger::class);

        // Inject htop_sqlite connection if not configured
        $connections = config('database.connections');
        if (! isset($connections['htop_sqlite'])) {
            config([
                'database.connections.htop_sqlite' => [
                    'driver' => 'sqlite',
                    'database' => storage_path('htop/htop.sqlite'),
                    'prefix' => '',
                ],
            ]);
        }

        $envPath = base_path('.env');

        if (file_exists($envPath) && strpos(file_get_contents($envPath), 'BROADCAST_DRIVER=') === false) {
            file_put_contents($envPath, PHP_EOL.'BROADCAST_DRIVER=reverb'.PHP_EOL, FILE_APPEND);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/htop.php', 'htop');

        config()->set('broadcasting.default', 'reverb');
    }
}
