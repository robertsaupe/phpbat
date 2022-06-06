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

use Symfony\Component\Console\Input\InputOption;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\Console\Logger as ConsoleLogger;

class Logger extends BasicCommandConfiguration {

    private const NOLOG_OPTION = 'no-log';

    protected function configure(): void {
        parent::configure();
        $this->setName('logger');
        $this->setDescription('Test the logger');
        $this->addOption(
            self::NOLOG_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Disable logging for this update'
        );
    }

    public function executeCommand(IO $io):int {

        $config = $this->getConfig($io);

        $logger = new ConsoleLogger(
            /** @phpstan-ignore-next-line */
            $this->getApplication(),
            $io->getInput()->getOption(self::NOLOG_OPTION) ? false : $config->getLogging()->getEnabled(),
            $io,
            $config->getLogging()->getPath(),
            'test',
            $config->getLogging()->getChmod(),
            verbosityKey:$config->getLogging()->getVerbosityKey()
        );

        $logger->error('test.1');
        $logger->warning('test.2');
        $logger->info('test.3');
        $logger->normal('test.4.1');
        $logger->write('test.4.2');
        $logger->verbose('test.5');
        $logger->veryverbose('test.6');
        $logger->debug('test.7.1');
        $logger->veryveryverbose('test.7.2');

        $logger->errorNoOutput('test.1');
        $logger->warningNoOutput('test.2');
        $logger->infoNoOutput('test.3');
        $logger->normalNoOutput('test.4.1');
        $logger->writeNoOutput('test.4.2');
        $logger->verboseNoOutput('test.5');
        $logger->veryverboseNoOutput('test.6');
        $logger->debugNoOutput('test.7.1');
        $logger->veryveryverboseNoOutput('test.7.2');

        return 0;
    }
}

?>