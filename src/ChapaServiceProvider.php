<?php

declare(strict_types=1);

namespace Vptrading\ChapaLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Vptrading\ChapaLaravel\Services\ChapaClient;

class ChapaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Model::unguard();
        AboutCommand::add('Chapa', fn () => [
            'Version' => '1.0.0',
        ]);
        $this->publishes([
            __DIR__.'/../config/chapa.php' => config_path('chapa.php'),
        ], 'chapa-config');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/chapa.php', 'chapa');

        $this->app->singleton('chapa', fn ($app) => new ChapaClient);
    }
}
