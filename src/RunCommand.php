<?php

declare(strict_types=1);

namespace Mdtt;

use Exception;
use LogicException;
use Mdtt\Definition\Definition;
use Mdtt\Exception\ExecutionException;
use Mdtt\Exception\FailFastException;
use Mdtt\LoadDefinition\Load;
use Mdtt\Notification\Email;
use MultipleIterator;
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
            )
            ->addOption(
                'fail-fast',
                null,
                InputOption::VALUE_NONE,
                'Specifies whether the test should return early in case of a failure.'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $progress = $output->section();
        $testSummary = $output->section();
        $report = new Report();

        // Setup.
        try {
            $progress->writeln("Loading test definitions", OutputInterface::VERBOSITY_DEBUG);

            /** @var array<string> $rawTestDefinitions */
            $rawTestDefinitions = $this->definitionLoader->scan([
              "tests/mdtt/*.yml",
              "tests/mdtt/*.yaml"
            ]);
            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = $this->definitionLoader->validate($rawTestDefinitions);
        } catch (IOException $exception) {
            $progress->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            return Command::INVALID;
        }

        /** @var bool $isSmokeTest */
        $isSmokeTest = $input->getOption('smoke-test');
        /** @var bool $isFailFast */
        $isFailFast = $input->getOption('fail-fast');
        /** @var string|null $notificationEmail */
        $notificationEmail = $input->getOption('email');

        // Tests run.
        try {
            $this->doRunTests($definitions, $isSmokeTest, $isFailFast, $report, $progress);
        } catch (Exception $exception) {
            $progress->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);

            $this->finalizeTestRun($report, $testSummary, $notificationEmail);

            $testSummary->writeln("<error>INVALID</error>");

            return Command::INVALID;
        }

        // Notification.
        $this->finalizeTestRun($report, $testSummary, $notificationEmail);

        if ($report->isFailure()) {
            $testSummary->writeln("<error>FAILED</error>");
            return Command::FAILURE;
        }

        $testSummary->writeln("<info>OK</info>");
        return Command::SUCCESS;
    }

    private function finalizeTestRun(
        Report $report,
        ConsoleSectionOutput $testSummary,
        ?string $notificationEmail
    ): void {
        $readableReport = sprintf(
            "Number of test definitions: %d\n".
            "Number of assertions made: %d\n" .
            "Number of failures: %d\n" .
            "Number of compared rows in source: %d\n" .
            "Number of compared rows in destination: %d",
            $report->getNumberOfTestDefinitions(),
            $report->getNumberOfAssertions(),
            $report->getNumberOfFailures(),
            $report->getSourceRowCount(),
            $report->getDestinationRowCount()
        );

        $testSummary->writeln($readableReport);

        if ($notificationEmail !== null) {
            try {
                $this->email->sendMessage("Test completed", $readableReport, $notificationEmail);
            } catch (Exception $exception) {
                $testSummary->writeln($exception->getMessage(), OutputInterface::VERBOSITY_QUIET);
            }
        }
    }

    /**
     * @param \Mdtt\Definition\Definition[] $definitions
     * @param bool $isSmokeTest
     * @param bool $isFailFast
     * @param \Mdtt\Report $report
     * @param \Symfony\Component\Console\Output\ConsoleSectionOutput $progress
     *
     * @return void
     */
    private function doRunTests(
        array $definitions,
        bool $isSmokeTest,
        bool $isFailFast,
        Report $report,
        ConsoleSectionOutput $progress
    ): void {
        foreach ($definitions as $definition) {
            $report->incrementNumberOfTestDefinitions();

            $progress->writeln(
                sprintf(
                    "Running the tests of definition id: %s",
                    $definition->getId()
                ),
                OutputInterface::VERBOSITY_VERBOSE
            );

            try {
                $isSmokeTest ?
                  $this->runSmokeTests($definition, $report, $progress, $isFailFast) :
                  $this->runTests($definition, $report, $progress, $isFailFast);
            } catch (FailFastException) {
                break;
            } catch (Exception $exception) {
                throw new ExecutionException($exception->getMessage());
            }
        }
    }

    private function runSmokeTests(
        Definition $definition,
        Report $report,
        ConsoleSectionOutput $progress,
        bool $isFailFast
    ): void {
        $source = $definition->getSource();
        $destination = $definition->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        $sourceRowCounts = iterator_count($sourceIterator);
        $destinationRowCounts = iterator_count($destinationIterator);

        try {
            $report->incrementNumberOfAssertions();

            Assert::assertSame(
                $sourceRowCounts,
                $destinationRowCounts
            );

            $progress->write('<info>P</info>');
        } catch (ExpectationFailedException) {
            $report->incrementNumberOfFailures();

            $progress->write('<error>F</error>');

            if ($isFailFast) {
                throw new FailFastException();
            }
        } catch (Exception $exception) {
            throw new ExecutionException($exception->getMessage());
        } finally {
            $report->setSourceRowCount($report->getSourceRowCount() + $sourceRowCounts);
            $report->setDestinationRowCount($report->getDestinationRowCount() + $destinationRowCounts);
        }
    }

    private function runTests(
        Definition $definition,
        Report $report,
        ConsoleSectionOutput $progress,
        bool $isFailFast
    ): void {
        $source = $definition->getSource();
        $destination = $definition->getDestination();

        $sourceIterator = $source->getIterator();
        $destinationIterator = $destination->getIterator();

        // Combining the iterators is required so that the tests can be run for every returned item.
        $combinedIterators = new MultipleIterator();
        $combinedIterators->attachIterator($sourceIterator);
        $combinedIterators->attachIterator($destinationIterator);

        foreach ($combinedIterators as [$sourceValue, $destinationValue]) {
            $report->incrementSourceRowCount();
            $report->incrementDestinationRowCount();

            $progress->writeln(
                sprintf(
                    "Comparing datasets, source: %s, destination: %s",
                    print_r($sourceValue, true),
                    print_r($destinationValue, true)
                ),
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
                    $report->incrementNumberOfAssertions();
                    $test->execute($sourceValue, $destinationValue);
                    $progress->write('<info>P</info>');
                } catch (ExpectationFailedException $exception) {
                    $report->incrementNumberOfFailures();
                    $progress->write('<error>F</error>');

                    $this->logger->emergency('Source and destination does not match.', [
                      'Definition' => $definition->getId(),
                      'Error' => $exception->getMessage(),
                    ]);

                    if ($isFailFast) {
                        throw new FailFastException();
                    }
                } catch (Exception $exception) {
                    throw new ExecutionException($exception->getMessage());
                }
            }
        }

        while ($sourceIterator->valid()) {
            $report->incrementSourceRowCount();
            $sourceIterator->next();
        }

        while ($destinationIterator->valid()) {
            $report->incrementDestinationRowCount();
            $destinationIterator->next();
        }
    }
}
