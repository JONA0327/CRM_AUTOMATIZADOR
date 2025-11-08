<?php

namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class EnsureLogDirectoryWritable
{
    public function __invoke(Logger $logger): void
    {
        $handlers = [];

        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handlers[] = $this->prepareHandler($handler);
                continue;
            }

            $handlers[] = $handler;
        }

        $logger->setHandlers($handlers);
    }

    private function prepareHandler(StreamHandler $handler): StreamHandler
    {
        $path = $handler->getUrl();

        if (str_starts_with($path, 'php://')) {
            return $handler;
        }

        $directory = \dirname($path);

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $directoryWritable = is_dir($directory) && is_writable($directory);
        $fileWritable = file_exists($path) ? is_writable($path) : $directoryWritable;

        if ($directoryWritable && $fileWritable) {
            return $handler;
        }

        $handler->close();

        $fallback = new StreamHandler('php://stderr', $handler->getLevel(), $handler->getBubble());

        if ($formatter = $handler->getFormatter()) {
            $fallback->setFormatter($formatter);
        }

        if (method_exists($handler, 'getFilePermission') && method_exists($fallback, 'setFilePermission')) {
            $fallback->setFilePermission($handler->getFilePermission());
        }

        if (method_exists($handler, 'getUseLocking') && method_exists($fallback, 'useLocking')) {
            $fallback->useLocking($handler->getUseLocking());
        }

        foreach ($handler->getProcessors() as $processor) {
            $fallback->pushProcessor($processor);
        }

        return $fallback;
    }
}

