<?php

namespace phpbat\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Hello extends Command
{
    protected static $defaultName = 'hello';

    protected function configure()
    {
        $this->setDescription('Outputs "Hello World"');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int {
        $output->writeln('Hello World');
        return 0;
    }
}

?>