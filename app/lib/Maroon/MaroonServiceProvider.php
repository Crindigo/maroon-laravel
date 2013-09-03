<?php

namespace Maroon;

use Illuminate\Support\ServiceProvider;

class MaroonServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ( $this->app->environment() != 'production' ) {
            $less = new LessCompiler($this->app);
            $less->compileLessFiles();
        }
    }

    public function register()
    {
        $this->app->bind('maroon.less_compiler', function($app) {
            return new LessCompiler($app);
        });

        $this->app['maroon.command.less_compile'] = $this->app->share(function($app) {
            return new \CompileLessCommand($app);
        });

        $this->commands('maroon.command.less_compile');
    }
}