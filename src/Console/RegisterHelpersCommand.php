<?php

namespace Elisame\HelperCreator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterHelpersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helpers:register {--force : Overrides the helper paths in composer.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all helpers inside App/Helpers in composer.json';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $composerPath = base_path('composer.json');
        $backupDir = storage_path('backups/composer');

        $this->createBackup($composerPath, $backupDir);
        $composerData = $this->loadComposerJson($composerPath);
        $helperPaths = $this->discoverHelpers();

        $this->badge('INFO', count($helperPaths) . ' Helpers inside App/Helpers.', 'info');

        $registered = $this->registerHelpers($composerData, $helperPaths);
        $this->badge('INFO', count($registered) . ' new helper' . (count($registered) === 1 ? '' : 's') . ' added.', 'info');

        $this->saveComposerJson($composerPath, $composerData);
        $this->cleanupBackups($backupDir);

        if (count($registered)) {
            $this->line('');
            $this->badge('SUMMARY', 'Helpers registered successfully inside App/Helpers:', 'info');
            $this->table(['Helpers'], collect($registered)->map(fn($h) => [$h])->toArray());
        }
    }

    protected function createBackup(string $composerPath, string $backupDir): void
    {
        if (!$this->isFeatureEnabled('backup')) {
            $this->info('Automatic backup is disabled via configuration.');
            return;
        }

        File::ensureDirectoryExists($backupDir);
        $timestamp = now()->format('Ymd_His');
        $backupFile = "{$backupDir}/composer_{$timestamp}.json";

        try {
            File::copy($composerPath, $backupFile);
            $this->badge('BACKUP', "composer.json backup created successfully inside 'storage/backups/composer'.", 'success');
        } catch (\Exception $e) {
            $this->badge('BACKUP', "Error creating backup: " . $e->getMessage(), 'error');
        }
    }

    protected function loadComposerJson(string $path): array
    {
        $content = file_get_contents($path);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->badge('ERROR', 'composer.json invalid.', 'error');
            exit(1);
        }

        $json['autoload']['files'] = $json['autoload']['files'] ?? [];
        return $json;
    }

    protected function discoverHelpers(): array
    {
        return collect(File::files(app_path('Helpers')))
            ->map(fn($file) => 'app/Helpers/' . $file->getFilename())
            ->values()
            ->toArray();
    }

    protected function registerHelpers(array &$composerData, array $helperPaths): array
    {
        $registered = [];

        foreach ($helperPaths as $helperPath) {
            if (!in_array($helperPath, $composerData['autoload']['files'])) {
                $composerData['autoload']['files'][] = $helperPath;
                $registered[] = $helperPath;
            } elseif ($this->option('force')) {
                $composerData['autoload']['files'] = array_filter(
                    $composerData['autoload']['files'],
                    fn($path) => $path !== $helperPath
                );
                $composerData['autoload']['files'][] = $helperPath;
                $registered[] = $helperPath;
            }
        }

        return $registered;
    }

    protected function saveComposerJson(string $path, array $data): void
    {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function cleanupBackups(string $backupDir): void
    {
        $backups = collect(File::files($backupDir))
            ->filter(fn($file) => str_ends_with($file->getFilename(), '.json'))
            ->sortByDesc(fn($file) => $file->getMTime())
            ->values();

        if ($backups->count() > 3) {
            $toDelete = $backups->slice(3);
            foreach ($toDelete as $file) {
                File::delete($file->getPathname());
                $this->badge('CLEANUP', "Oldest backup removed successfully from 'storage/backups/composer'.", 'error');
                break; // Remove apenas o mais antigo
            }
        }
    }

    protected function badge(string $label, string $message, string $color = 'info'): void
    {
        $colors = [
            'info' => 'comment',
            'success' => 'info',
            'error' => 'error',
            'warn' => 'warn',
        ];

        $style = $colors[$color] ?? 'comment';
        $this->{$style}("[$label] $message");
    }

    protected function isFeatureEnabled(string $feature): bool
    {
        return config("helper-creator.{$feature}_enabled", true);
    }
}
