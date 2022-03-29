<?php

declare(strict_types=1);

namespace Mdtt;

use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\DefaultLoader;
use Mdtt\Notification\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RunCommand extends Command
{
    private Email $email;

    public function __construct(Email $email, string $name = null)
    {
        parent::__construct($name);
        $this->email = $email;
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
        $logger = new ConsoleLogger($output);
        try {
            $logger->info("Loading test definitions");

            /** @var \Mdtt\Definition\Definition[] $definitions */
            $definitions = (new DefaultLoader($logger))->validate();
            foreach ($definitions as $definition) {
                $definition->runTests();
            }
        } catch (IOException $exception) {
            $logger->error($exception->getMessage());
            return Command::FAILURE;
        }

        try {
            $this->email->sendMessage("Test completed", "Test completed", "subhojit.paul@axelerant.com");
        } catch (SetupException $exception) {
            $logger->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }
}
