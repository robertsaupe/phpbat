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

use Throwable;
use robertsaupe\phpbat\Console\IO;

class Error extends BaseCommand {

    protected function configure() {
        $this->setName('error');
        $this->setDescription('Outputs an Error');
    }

    public function executeCommand(IO $io):int {
        $io->writeln($this->getApplication()->getHelp());
        $io->newLine();

        try {
            $var = 5 / 0;
            $io->writeln('$var: ' . $var);
        } catch (Throwable $throwable) {

            $io->error('Could not read "$var".');

            return 1;
        }

        return 0;
    }
}

?>