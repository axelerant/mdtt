<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\DefaultLoader;
use Mdtt\Notification\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RunCommand extends Command
{
    private Email $email;
    private LoggerInterface $logger;

    public function __construct(Email $email, LoggerInterface $logger, string $name = null)
    {
        parent::__construct($name);
        $this->email = $email;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Compare the source and destination data.')
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email address where the notification will be sent when test completes.'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $this->logger->info("Loading test definitions");

            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = (new DefaultLoader($this->logger))->validate();
            foreach ($definitions as $definition) {
                $definition->runTests();
            }
        } catch (IOException $exception) {
            $this->logger->error($exception->getMessage());
            return Command::FAILURE;
        }

        /** @var string|null $email */
        $email = $input->getOption('email');
        if ($email !== null) {
            try {
                $this->email->sendMessage("Test completed", "Test completed", $email);
            } catch (SetupException $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
