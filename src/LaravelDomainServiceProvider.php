<?php

namespace Uatthaphon\LaravelDomain;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelDomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->configurePublishing();
        $this->configureCommands();
        $this->resolveFactoryDomainName();
    }

    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/laravel-domain.php' => config_path('laravel-domain.php'),
        ], 'laravel-domain-config');
    }

    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\CreateBaseCommand::class,
            Console\CreateApiCommand::class,
        ]);
    }

    private function resolveFactoryDomainName()
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $namespace = 'Database\\Factories\\';
            $appNamespace = 'App\\';
            $domainNamespace = $appNamespace . 'Domain\\';

            $guessNamespace = '';

            if (Str::startsWith($modelName, $domainNamespace)) {
                $guessNamespace = 'Domain\\' . Str::between($modelName, $domainNamespace, 'Models\\');
            }

            $modelName = Str::afterLast($modelName, '\\');

            return $namespace . $guessNamespace . $modelName . 'Factory';
        });
    }
}
