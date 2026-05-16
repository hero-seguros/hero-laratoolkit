<?php

namespace HeroLaraToolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use HeroLaraToolkit\Helpers\ValidatorHelper;

class HeroLaraToolkitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerCommandOverrides();
    }

    /**
     * Replace the native Laravel `make:` command singletons with our domain-aware
     * overrides. We do this via $this->app->extend(FQCN::class, ...) so that
     * (a) every native flag continues to work, and (b) we don't need to publish
     * a custom command — Laravel resolves the same FQCN from the container.
     */
    protected function registerCommandOverrides(): void
    {
        $overrides = [
            \Illuminate\Routing\Console\ControllerMakeCommand::class => \HeroLaraToolkit\Commands\MakeControllerCommand::class,
            \Illuminate\Foundation\Console\RequestMakeCommand::class => \HeroLaraToolkit\Commands\MakeRequestCommand::class,
            \Illuminate\Foundation\Console\ResourceMakeCommand::class => \HeroLaraToolkit\Commands\MakeResourceCommand::class,
            \Illuminate\Foundation\Console\PolicyMakeCommand::class => \HeroLaraToolkit\Commands\MakePolicyCommand::class,
            \Illuminate\Foundation\Console\JobMakeCommand::class => \HeroLaraToolkit\Commands\MakeJobCommand::class,
            \Illuminate\Foundation\Console\ConsoleMakeCommand::class => \HeroLaraToolkit\Commands\MakeConsoleCommand::class,
            \Illuminate\Foundation\Console\TestMakeCommand::class => \HeroLaraToolkit\Commands\MakeTestCommand::class,
            \Illuminate\Routing\Console\MiddlewareMakeCommand::class => \HeroLaraToolkit\Commands\MakeMiddlewareCommand::class,
            \Illuminate\Foundation\Console\RuleMakeCommand::class => \HeroLaraToolkit\Commands\MakeRuleCommand::class,
            \Illuminate\Foundation\Console\ObserverMakeCommand::class => \HeroLaraToolkit\Commands\MakeObserverCommand::class,
        ];

        foreach ($overrides as $native => $replacement) {
            $this->app->extend($native, function () use ($replacement) {
                return new $replacement($this->app['files']);
            });
        }
    }

    public function boot()
    {
        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::cpf($value);
        });

        Validator::extend('cnpj', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::cnpj($value);
        });

        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::phone($value);
        });

        Validator::extend('cellphone', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::cellphone($value);
        });

        Validator::extend('cep', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::cep($value);
        });

        Validator::extend('passport', function ($attribute, $value, $parameters, $validator) {
            return ValidatorHelper::passport($value);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \HeroLaraToolkit\Commands\MakeServiceCommand::class,
                \HeroLaraToolkit\Commands\MakeRepositoryCommand::class,
                \HeroLaraToolkit\Commands\MakeAdapterCommand::class,
                \HeroLaraToolkit\Commands\MakeHelperCommand::class,
            ]);
        }

        $this->autoRegisterRepositoryProvider();
    }

    /**
     * If the consumer service has an App\Providers\RepositoryServiceProvider
     * (created/maintained by `make:repository`), register it automatically.
     * Avoids editing AppServiceProvider or bootstrap/providers.php.
     */
    protected function autoRegisterRepositoryProvider(): void
    {
        $provider = 'App\\Providers\\RepositoryServiceProvider';

        if (class_exists($provider)) {
            $this->app->register($provider);
        }
    }
}
