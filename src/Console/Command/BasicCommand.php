<?php

declare(strict_types=1);

/*
 * This file is part of the robertsaupe/phpbat package.
 *
 * (c) Robert Saupe <mail@robertsaupe.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace robertsaupe\phpbat\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\Console\OutputFormatterStyleConfigurator;

/**
 * @internal
 */
abstract class BasicCommand extends Command {

    abstract protected function executeCommand(IO $io): int;

    protected function execute(InputInterface $input, OutputInterface $output): int {
        OutputFormatterStyleConfigurator::configure($output);
        return $this->executeCommand(new IO($input, $output));
    }
}

?>