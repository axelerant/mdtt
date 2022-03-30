<?php

declare(strict_types=1);

namespace Mdtt\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class DefaultLogger implements LoggerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->logger->pushHandler(new StreamHandler('php://stderr'));
    }

    /**
     * @inheritDoc
     */
    public function emergency(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->emergency($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->alert($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->critical($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->error($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->warning($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->notice($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug(
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->debug($message, $context);
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param mixed             $level   The log level
     * @param string|\Stringable $message The log message
     * @param array<string> $context The log context
     *
     * @phpstan-param LogLevel::* $level
     */
    public function log(
        $level,
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->log($level, $message, $context);
    }
}
