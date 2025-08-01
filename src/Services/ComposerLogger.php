<?php

namespace Elisame\HelperCreator\Services;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class ComposerLogger
{
    protected Logger $logger;

    public function __construct()
    {
        $path = storage_path('logs/helper-creator.log');

        $stream = new StreamHandler($path, Level::Debug);
        $stream->setFormatter(new LineFormatter(null, null, true, true));

        $this->logger = new Logger('helper-creator');
        $this->logger->pushHandler($stream);
    }

    public function info(string $message): void
    {
        $this->logger->info($message);
    }

    public function warning(string $message): void
    {
        $this->logger->warning($message);
    }

    public function error(string $message): void
    {
        $this->logger->error($message);
    }

    public function debug(string $message): void
    {
        $this->logger->debug($message);
    }
}
