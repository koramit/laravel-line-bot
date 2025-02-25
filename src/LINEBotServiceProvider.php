<?php

namespace Koramit\LaravelLINEBot;

use Illuminate\Support\ServiceProvider;

class LINEBotServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__.'/database/migrations' => database_path('migrations'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/route.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/line.php', 'line');
    }
}
