<?php

namespace NocturnalSm\Approval\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use NocturnalSm\Approval\ApprovalManager;

class ApprovalServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind('approval',function() {
            return new \NocturnalSm\Approval\ApprovalManager;
        });        
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('approval.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'approval'
        );
        foreach (glob('/../Policies/*.php') as $filename){            
            $nopath = substr($filename, strrpos($filename,"/")+1);
            list($file, $ext) = explode(".", $nopath);
            $class = 'NocturnalSm\Approval\Policies' ."\\" .$file;
            $config = $class::getConfig();            
            config(["approval.policies" => array_merge($config, config('approval.policies'))]);
        }        
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/approval');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/approval';
        }, \Config::get('view.paths')), [$sourcePath]), 'approval');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/approval');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'approval');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'approval');
        }
    }

    /**
     * Register an additional directory of factories.
     * 
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
