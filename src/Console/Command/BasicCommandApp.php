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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
abstract class BasicCommandApp extends BasicCommand {

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln($this->getApplication()->getHelp());
        $output->writeln('');
        return parent::execute($input, $output);
    }
}

?>