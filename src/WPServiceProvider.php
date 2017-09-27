<?php

namespace meesoverdevest\wp_on_laravel;

use Illuminate\Support\ServiceProvider;
use meesoverdevest\wp_on_laravel\Commands\InstallWordPress;

class WPServiceProvider extends ServiceProvider
{

    protected $commands = [
        'meesoverdevest\wp_on_laravel\Commands\InstallWordPress'
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // https://laravel.io/forum/09-13-2014-create-new-database-and-tables-on-the-fly
        // http://laraveldaily.com/how-to-create-a-laravel-5-package-in-10-easy-steps/
        // https://laravel.com/docs/5.4/packages
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallWordPress::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->commands($this->commands);
        $this->app->make('meesoverdevest\wp_on_laravel\controllers\WPSyncController');
    }
}

