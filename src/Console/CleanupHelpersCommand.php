<?php

namespace Elisame\HelperCreator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupHelpersCommand extends Command
{
    protected $signature = 'helper:cleanup {--dry-run : Only displays what would be removed}';
    protected $description = 'Synchronize and remove invalid files from autoload.files key in composer.jsons';

    public function handle(): void
    {
        $composerPath = base_path('composer.json');

        if (!File::exists($composerPath)) {
            $this->error('composer.json not found.');
            return;
        }

        $composer = json_decode(File::get($composerPath), true);

        if (!isset($composer['autoload']['files'])) {
            $this->info('No files found inside autoload.files.');
            return;
        }

        $files = $composer['autoload']['files'];
        $invalidFiles = [];

        foreach ($files as $file) {
            $fullPath = base_path($file);
            if (!File::exists($fullPath)) {
                $invalidFiles[] = $file;
            }
        }

        if (empty($invalidFiles)) {
            $this->info('All files inside autoload.files key are valid.');
            return;
        }

        $this->warn('Invalid files found:');
        foreach ($invalidFiles as $file) {
            $this->line("- $file");
        }

        if ($this->option('dry-run')) {
            $this->comment('Execution with flag --dry-run. No changes were made.');
            return;
        }

        // Backup
        $backupPath = base_path('composer.backup.json');
        File::copy($composerPath, $backupPath);
        $this->info("Backup created: composer.backup.json");

        // Remover inválidos
        $composer['autoload']['files'] = array_values(array_diff($files, $invalidFiles));
        File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('composer.json updated with valid files only.');

        // Atualizar autoload
        $this->runComposerDumpAutoload();
    }

    protected function runComposerDumpAutoload(): void
    {
        $this->info('Running composer dump-autoload...');
        exec('composer dump-autoload', $output, $status);

        if ($status === 0) {
            $this->info('Autoload updated successfully.');
        } else {
            $this->error('Failed to execute composer dump-autoload.');
        }
    }
}
