<?php

namespace Elisame\HelperCreator;

use Illuminate\Support\ServiceProvider;
use Elisame\HelperCreator\Services\ComposerLogger;
use Elisame\HelperCreator\Console\CleanupHelpersCommand;
use Elisame\HelperCreator\Console\RegisterHelpersCommand;
use Elisame\HelperCreator\Console\MakeHelperCommand;
use Elisame\HelperCreator\Console\RestoreComposerBackupCommand;

class HelperCreatorServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/config/helper-creator.php';

    public function boot(): void
    {
        $this->publishConfig();

        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->bindServices();
        $this->registerConsoleCommands(); // Opcional: se quiser registrar também fora do console
    }

    private function publishConfig(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('helper-creator.php'),
        ], 'helper-creator-config');
    }

    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'helper-creator');
    }

    private function bindServices(): void
    {
        $this->app->singleton(ComposerLogger::class, fn() => new ComposerLogger());
    }

    private function registerConsoleCommands(): void
    {
        $this->commands([
            RegisterHelpersCommand::class,
            MakeHelperCommand::class,
            RestoreComposerBackupCommand::class,
            CleanupHelpersCommand::class
        ]);
    }
}
