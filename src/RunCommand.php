<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\Load;
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
    private Load $definitionLoader;

    public function __construct(Email $email, LoggerInterface $logger, Load $loader, string $name = null)
    {
        parent::__construct($name);
        $this->email = $email;
        $this->logger = $logger;
        $this->definitionLoader = $loader;
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
            )
            ->addOption(
              'smoke-test',
              null,
              InputOption::VALUE_NONE,
              'Specifies whether it should perform a smoke test, instead of a detailed row by row comparison.'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $this->logger->info("Loading test definitions");

            /** @var array<string> $rawTestDefinitions */
            $rawTestDefinitions = $this->definitionLoader->scan([
              "tests/mdtt/*.yml",
              "tests/mdtt/*.yaml"
            ]);
            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = $this->definitionLoader->validate($rawTestDefinitions);

            /** @var bool $isSmokeTest */
            $isSmokeTest = $input->getOption('smoke-test');
            foreach ($definitions as $definition) {
                $isSmokeTest ? $definition->runSmokeTests() : $definition->runTests();
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
