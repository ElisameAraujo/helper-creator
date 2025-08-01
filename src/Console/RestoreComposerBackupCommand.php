<?php

namespace Elisame\HelperCreator\Console;

use Illuminate\Console\Command;
use Elisame\HelperCreator\Services\ComposerBackupManager;

class RestoreComposerBackupCommand extends Command
{
    protected $signature = 'helper:restore-backup {--dry-run : Simulates changes to files before saving them.}';
    protected $description = 'Restore the most recent composer.json backup from storage/composer/backup';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $manager = app(ComposerBackupManager::class);

        if ($dryRun) {
            $diff = $manager->getRequireDiff();

            if (empty($diff)) {
                $this->info('✅ No differences found in section "require".');
                return Command::SUCCESS;
            }

            $this->info('📦 Differences found in the section "require":');

            foreach ($diff as $package => $info) {
                if ($info['status'] === 'added') {
                    $this->line("➕ Added: {$package} => {$info['version']}");
                } elseif ($info['status'] === 'changed') {
                    $this->line("🔄 Updated: {$package} => {$info['from']} → {$info['to']}");
                }
            }
        }

        if ($manager->restore()) {
            $this->info('✅ composer.json successfully restored from the most recent backup.');
            return Command::SUCCESS;
        }

        $this->error('❌ Failed to restore composer.json. Check the logs for more details.');
        return Command::FAILURE;
    }
}
