<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\Load;
use Mdtt\Notification\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RunCommand extends Command
{
    private Email $email;

    private Load $definitionLoader;

    public function __construct(Email $email, Load $loader, string $name = null)
    {
        parent::__construct($name);
        $this->email = $email;
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
            $output->writeln("Loading test definitions", OutputInterface::VERBOSITY_VERY_VERBOSE);

            /** @var array<string> $rawTestDefinitions */
            $rawTestDefinitions = $this->definitionLoader->scan([
              "tests/mdtt/*.yml",
              "tests/mdtt/*.yaml"
            ]);
            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = $this->definitionLoader->validate($rawTestDefinitions);

            $report = new Report();
            $report->setNumberOfTestDefinitions(count($definitions));

            /** @var bool $isSmokeTest */
            $isSmokeTest = $input->getOption('smoke-test');
            foreach ($definitions as $definition) {
                $output->writeln(
                    sprintf(
                        "Running the tests of definition id: %s",
                        $definition->getId()
                    ),
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                $isSmokeTest ? $definition->runSmokeTests($report) : $definition->runTests($report);
            }
        } catch (IOException $exception) {
            $output->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            return Command::INVALID;
        }

        /** @var string|null $email */
        $email = $input->getOption('email');
        if ($email !== null) {
            try {
                $this->email->sendMessage("Test completed", "Test completed", $email);
            } catch (SetupException $exception) {
                $output->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            }
        }

        $output->writeln(sprintf("Number of test definitions: %d", $report->getNumberOfTestDefinitions()));
        $output->writeln(sprintf("Number of assertions: %d", $report->getNumberOfAssertions()));
        $output->writeln(sprintf("Number of failures: %d", $report->getNumberOfFailures()));
        $output->writeln(sprintf("Number of rows in source: %d", $report->getSourceRowCount()));
        $output->writeln(sprintf("Number of rows in destination: %d", $report->getDestinationRowCount()));

        if ($report->isFailure()) {
            $output->writeln("<error>FAILED</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>OK</info>");
        return Command::SUCCESS;
    }
}
