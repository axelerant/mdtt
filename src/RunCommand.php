<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\Definition\Definition;
use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\Load;
use Mdtt\Notification\Email;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
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
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $progress = $output->section();
        $testSummary = $output->section();

        try {
            $progress->writeln("Loading test definitions", OutputInterface::VERBOSITY_VERY_VERBOSE);

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
                $progress->writeln(
                    sprintf(
                        "Running the tests of definition id: %s",
                        $definition->getId()
                    ),
                    OutputInterface::VERBOSITY_VERBOSE
                );

                $isSmokeTest ?
                  $this->runSmokeTests($definition, $report, $progress) :
                  $this->runTests($definition, $report, $progress);
            }
        } catch (IOException $exception) {
            $progress->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            return Command::INVALID;
        }

        // TODO: move this into a private method.
        /** @var string|null $email */
        $email = $input->getOption('email');
        if ($email !== null) {
            try {
                $this->email->sendMessage("Test completed", "Test completed", $email);
            } catch (SetupException $exception) {
                $progress->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            }
        }

        // todo: move this inside private method.
        $testSummary->writeln(sprintf("Number of test definitions: %d", $report->getNumberOfTestDefinitions()));
        $testSummary->writeln(sprintf("Number of assertions: %d", $report->getNumberOfAssertions()));
        $testSummary->writeln(sprintf("Number of failures: %d", $report->getNumberOfFailures()));
        $testSummary->writeln(sprintf("Number of rows in source: %d", $report->getSourceRowCount()));
        $testSummary->writeln(sprintf("Number of rows in destination: %d", $report->getDestinationRowCount()));

        if ($report->isFailure()) {
            $testSummary->writeln("<error>FAILED</error>");
            return Command::FAILURE;
        }

        $testSummary->writeln("<info>OK</info>");
        return Command::SUCCESS;
    }

    private function runSmokeTests(Definition $definition, Report $report, ConsoleSectionOutput $progress): void
    {
        $assertionCount = 0;
        $failureCount = 0;

        $source = $definition->getSource();
        $destination = $definition->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        $sourceRowCounts = iterator_count($sourceIterator);
        $destinationRowCounts = iterator_count($destinationIterator);

        try {
            $assertionCount++;

            Assert::assertSame(
                $sourceRowCounts,
                $destinationRowCounts
            );

            $progress->write('<info>P</info>');
        } catch (ExpectationFailedException) {
            $failureCount++;

            $progress->write('<error>F</error>');
        }

        $report->setNumberOfAssertions($assertionCount);
        $report->setNumberOfFailures($failureCount);
        $report->setSourceRowCount($sourceRowCounts);
        $report->setDestinationRowCount($destinationRowCounts);
    }

    private function runTests(Definition $definition, Report $report, ConsoleSectionOutput $progress): void
    {
        $assertionCount = 0;
        $failureCount = 0;
        $sourceCount = 0;
        $destinationCount = 0;

        $source = $definition->getSource();
        $destination = $definition->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        // Combining the iterators is required so that the tests can be run for every returned item.
        $combinedIterators = new \MultipleIterator();
        $combinedIterators->attachIterator($sourceIterator);
        $combinedIterators->attachIterator($destinationIterator);

        foreach ($combinedIterators as [$sourceValue, $destinationValue]) {
            $sourceCount++;
            $destinationCount++;

            $progress->writeln(
                sprintf("Comparing datasets, source: %s, destination: %s", print_r($sourceValue, true), print_r($destinationValue, true)),
                OutputInterface::VERBOSITY_DEBUG
            );

            foreach ($definition->getTests() as $test) {
                if ($test->getTransform()) {
                    $progress->writeln(
                        sprintf("Applying transform: %s on source", $test->getTransform()->name()),
                        OutputInterface::VERBOSITY_DEBUG
                    );
                }

                try {
                    $assertionCount++;
                    $test->execute($sourceValue, $destinationValue);
                    $progress->write('<info>P</info>');
                } catch (ExpectationFailedException $exception) {
                    $failureCount++;
                    $progress->write('<error>F</error>');

                    $this->logger->emergency("Source and destination does not match.", [
                      "Source" => $sourceValue[$test->getSourceField()],
                      "Destination" => $destinationValue[$test->getDestinationField()],
                    ]);
                }
            }
        }

        while ($sourceIterator->valid()) {
            $sourceCount++;
            $sourceIterator->next();
        }

        while ($destinationIterator->valid()) {
            $destinationCount++;
            $destinationIterator->next();
        }

        $report->setNumberOfAssertions($assertionCount);
        $report->setNumberOfFailures($failureCount);
        $report->setSourceRowCount($sourceCount);
        $report->setDestinationRowCount($destinationCount);
    }
}
