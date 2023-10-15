<?php

namespace Wdelfuego\NovaWizard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;
use Wdelfuego\NovaWizard\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        $this->publishes([
            __DIR__.'/../config/nova-wizard.php' => config_path('nova-wizard.php'),
        ], 'config');
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        foreach(config('nova-wizard', []) as $wizardKey => $wizardConfig)
        {
          if(is_array($wizardConfig))
          {
              if(!isset($wizardConfig['uri']))
              {
                  throw new \Exception("Missing config option `uri` for wizard `$wizardKey` in config/nova-wizard.php");
              }
              else if(!strlen(trim($wizardConfig['uri'])))
              {
                  throw new \Exception("Empty config option `uri` for wizard `$wizardKey` in config/nova-wizard.php");
              }
              else
              {
                  Nova::router(['nova', Authenticate::class, Authorize::class], $wizardConfig['uri'])
                      ->group(__DIR__.'/../routes/inertia.php');

                  Route::middleware(['nova', Authorize::class])
                      ->prefix('nova-vendor/wdelfuego/nova-wizard/'.$wizardConfig['uri'])
                      ->group(__DIR__.'/../routes/api.php');
              }
          }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
