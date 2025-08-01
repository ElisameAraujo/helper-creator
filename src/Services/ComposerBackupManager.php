<?php

namespace Elisame\HelperCreator\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Elisame\HelperCreator\Services\ComposerLogger;

class ComposerBackupManager
{
    protected string $backupPath;
    protected string $targetPath;
    protected ComposerLogger $logger;
    public function __construct(ComposerLogger $logger)
    {
        $this->backupPath = storage_path('backups/composer');
        $this->targetPath = base_path('composer.json');
        $this->logger = $logger;
    }

    public function restore(): bool
    {
        if (!File::exists($this->backupPath)) {
            $logger = new ComposerLogger();
            $logger->warning("Backup path not found: {$this->backupPath}");
            return false;
        }

        $files = collect(File::files($this->backupPath))
            ->filter(fn($file) => preg_match('/^composer_\d{8}_\d{6}\.json$/', $file->getFilename()))
            ->sortByDesc(fn($file) => $file->getMTime());

        $latest = $files->first();

        if (!$latest) {
            $logger = new ComposerLogger();
            $logger->warning("No timestamped composer.json backups found in: {$this->backupPath}");
            return false;
        }

        try {
            $logger = new ComposerLogger();
            $current = json_decode(File::get($this->targetPath), true);
            $backup = json_decode(File::get($latest->getPathname()), true);

            // Mescla dependências
            $mergedRequire = array_merge($backup['require'] ?? [], $current['require'] ?? []);
            $mergedRequireDev = array_merge($backup['require-dev'] ?? [], $current['require-dev'] ?? []);

            $merged = $backup;
            $merged['require'] = $mergedRequire;
            $merged['require-dev'] = $mergedRequireDev;

            // Backup automático antes de sobrescrever
            $timestamp = now()->format('Ymd_His');
            $autoBackupPath = $this->backupPath . "/composer_autobackup_{$timestamp}.json";
            File::copy($this->targetPath, $autoBackupPath);

            $logger->info("Backup automático criado: {$autoBackupPath}");

            // Salva o composer.json mesclado
            File::put($this->targetPath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $logger->info("composer.json mesclado com sucesso entre backup e versão atual.");

            return true;
        } catch (Exception $e) {
            $logger->error("Erro ao mesclar composer.json: " . $e->getMessage());
            return false;
        }
    }

    public function getLatestBackupPath(): ?string
    {
        $backupDir = storage_path('backups/composer');

        if (!is_dir($backupDir)) {
            $logger = new ComposerLogger();
            $logger->error("Diretório de backup não encontrado: {$backupDir}");
            return null;
        }

        $files = collect(File::files($backupDir))
            ->filter(fn($file) => str_ends_with($file->getFilename(), '.json'))
            ->sortByDesc(fn($file) => $file->getMTime());

        $latest = $files->first();

        if (!$latest) {
            $logger = new ComposerLogger();
            $logger->warning("Nenhum arquivo de backup encontrado em {$backupDir}");
            return null;
        }

        return $latest->getPathname();
    }

    public function getMergedComposerJson(): array
    {
        $latestBackup = $this->getLatestBackupPath();
        $backupData = json_decode(file_get_contents($latestBackup), true);
        $currentData = json_decode(file_get_contents(base_path('composer.json')), true);

        $merged = $currentData;

        foreach ($backupData['require'] ?? [] as $package => $version) {
            if (!isset($merged['require'][$package])) {
                $merged['require'][$package] = $version;
            }
        }

        return $merged;
    }

    public function getRequireDiff(): array
    {
        $latestBackup = $this->getLatestBackupPath();
        if (!$latestBackup) return [];

        $backupData = json_decode(file_get_contents($latestBackup), true);
        $currentData = json_decode(file_get_contents(base_path('composer.json')), true);

        $backupRequire = $backupData['require'] ?? [];
        $currentRequire = $currentData['require'] ?? [];

        $diff = [];

        foreach ($backupRequire as $package => $version) {
            if (!isset($currentRequire[$package])) {
                $diff[$package] = [
                    'status' => 'added',
                    'version' => $version
                ];
            } elseif ($currentRequire[$package] !== $version) {
                $diff[$package] = [
                    'status' => 'changed',
                    'from' => $currentRequire[$package],
                    'to' => $version
                ];
            }
        }

        return $diff;
    }
}
