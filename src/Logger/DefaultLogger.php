<?php

declare(strict_types=1);

namespace Mdtt\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class DefaultLogger implements LoggerInterface
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../var/log/test.log', Logger::DEBUG));
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
     * @inheritDoc
     */
    public function log(
        $level,
        \Stringable|string $message,
        array $context = []
    ): void {
        $this->logger->log($level, $message, $context);
    }
}
