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

class Logger extends ConfigurationBaseCommand {

    protected function configure(): void {
        parent::configure();
        $this->setName('logger');
        $this->setDescription('Test the logger');
    }

    public function executeCommand(IO $io):int {
        $io->writeln($this->getApplication()->getHelp());
        $io->newLine();

        $config = $this->getConfig($io);

        $logger = new ConsoleLogger($config->getLoggingEnabled(), $config->getLoggingPath(), 'test', $config->getLoggingVerbosity(), $config->getLoggingchmod(), $io);

        $logger->error('test.1');
        $logger->warning('test.2');
        $logger->info('test.3');
        $logger->write('test.4');
        $logger->verbose('test.5');
        $logger->very_verbose('test.6');
        $logger->debug('test.7');

        print_r($logger->getAllFormattedMessagesByVerbosity(isHTML:false));

        print_r($logger->getAllFormattedMessagesByVerbosity(isHTML:true));

        return 0;
    }
}

?>