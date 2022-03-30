<?php

declare(strict_types=1);

namespace Mdtt\Notification;

use Mdtt\Exception\SetupException;
use Psr\Log\LoggerInterface;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use SendGrid\Mail\TypeException;

class Email implements Notification
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(
        string $title,
        string $message,
        string $receiver
    ): void {
        /** @var array<string> $receivers */
        $receivers = explode(',', $receiver);
        /** @var array<\SendGrid\Mail\To> $receivers */
        $receivers = array_map(static function (string $value) {
            return new To(trim($value));
        }, $receivers);
        /** @var string|false $fromAddress */
        $fromAddress = getenv("FROM_EMAIL");
        /** @var string|false $sendGridApiKey */
        $sendGridApiKey = getenv("SENDGRID_API_KEY");

        if ($fromAddress === false) {
            throw new SetupException("From address not specified in environment variable");
        }

        if ($sendGridApiKey === false) {
            throw new SetupException("Sendgrid API key is not configured.");
        }

        $email = new Mail();
        try {
            $email->setFrom($fromAddress);
            $email->addTos($receivers);
            $email->setSubject($title);
            $email->addContent("text/plain", $message);
        } catch (TypeException $e) {
            $this->logger->error($e->getMessage());
        }

        $sendgrid = new SendGrid($sendGridApiKey);

        try {
            $sendgrid->send($email);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
