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

use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\Console\Logger as ConsoleLogger;

class Logger extends BasicCommandConfiguration {

    protected function configure(): void {
        parent::configure();
        $this->setName('logger');
        $this->setDescription('Test the logger');
    }

    public function executeCommand(IO $io):int {
        $config = $this->getConfig($io);
        $logger = new ConsoleLogger($config->getLoggingEnabled(), $io, $config->getLoggingPath(), 'test', $config->getLoggingchmod(), verbosityKey:$config->getLoggingVerbosity());
        $logger->error('test.1');
        $logger->warning('test.2');
        $logger->info('test.3');
        $logger->normal('test.4.1');
        $logger->write('test.4.2');
        $logger->verbose('test.5');
        $logger->veryverbose('test.6');
        $logger->debug('test.7.1');
        $logger->veryveryverbose('test.7.2');
        return 0;
    }
}

?>