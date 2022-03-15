<?php

namespace Mdtt;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command {

  protected function configure(): void {
    $this->setName('run')
      ->setDescription('Compare the source and destination data.');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln("Hello world!");
    return Command::SUCCESS;
  }

}
