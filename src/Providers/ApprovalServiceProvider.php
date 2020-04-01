<?php

namespace NocturnalSm\Approval\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use NocturnalSm\Approval\ApprovalManager;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

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
    public function boot(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('approval.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../Database/Migrations/create_approval_table.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');
        $this->registerConfig();
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CreatePolicy::class,
                Commands\CreateApprover::class
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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
        foreach (glob(__DIR__.'/../Policies/*.php') as $filename){            
            $nopath = substr($filename, strrpos($filename,"/")+1);
            list($file, $ext) = explode(".", $nopath);
            $class = 'NocturnalSm\Approval\Policies' ."\\" .$file;
            config(["approval.policies" => $class::getConfig()]);
        }        
    }
    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_approval_table.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_approval_table.php")
            ->first();
    }
}
