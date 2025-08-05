<?php 
namespace AmrEljako\FawaterkPayment;

use Illuminate\Support\ServiceProvider;

class FawaterkServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fawaterk.php', 'fawaterk');

        $this->app->singleton(Fawaterk::class, function () {
            return new Fawaterk(config('fawaterk.token'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/fawaterk.php' => config_path('fawaterk.php'),
        ], 'config');
    }
}