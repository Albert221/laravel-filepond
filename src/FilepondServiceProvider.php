<?php

namespace Albert221\Filepond;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class FilepondServiceProvider extends ServiceProvider
{
    private const CONFIG_FILE = __DIR__ . '/../config/filepond.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, 'filepond');

        $this->app->singleton(FilepondSerializer::class, function (Application $app) {
            return new FilepondSerializer(
                $app->make(Encrypter::class),
                config('filepond.upload_temporary_dir', realpath(sys_get_temp_dir()))
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_FILE => config_path('filepond.php'),
        ], 'filepond');

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'filepond',
            'namespace' => '\Albert221\Filepond',
            'middleware' => config('filepond.middleware', []),
        ], function () {
            Route::post('/', 'FilepondController@process');
            Route::delete('/', 'FilepondController@revoke');
        });
    }
}
