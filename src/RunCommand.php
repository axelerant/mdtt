<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\LoadDefinition\DefaultLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RunCommand extends Command
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, string $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setName('run')
          ->setDescription('Compare the source and destination data.');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $this->logger->info("Loading test definitions");

            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = (new DefaultLoader())->validate();
            foreach ($definitions as $definition) {
                $definition->runTests();
            }
        } catch (IOException $exception) {
            $this->logger->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
