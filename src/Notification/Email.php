<?php

declare(strict_types=1);

namespace Mdtt\Notification;

use Mdtt\Exception\SetupException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email implements Notification
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
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
        $receivers = explode(',', $receiver);
        $receivers = array_map('trim', $receivers);
        /** @var string|false $from_address */
        $from_address = getenv("FROM_EMAIL");

        if ($from_address === false) {
            throw new SetupException("From address not specified in environment variable");
        }

        $email = (new SymfonyEmail())
          ->from((new Address($from_address)))
          ->to(...$receivers)
          ->subject($title)
          ->html($message)
          ->text($message);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
