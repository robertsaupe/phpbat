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
use Symfony\Component\Filesystem\Exception\IOException;
use robertsaupe\phpbat\Console\IO;

class ConfigurationCreate extends ConfigurationBaseCommand {

    private const FILE_NAME = 'phpbat.json';
    private const DEFAULT_FILE = __DIR__.'/../../../res/default.json';

    private const FORCE_OPTION = 'force';
    private const FORCE_OPTION_SHORT = 'f';

    protected function configure(): void {
        parent::configure();
        $this->setName('configuration:create');
        $this->setAliases(array('conf:create', 'cfg:create', 'cfg:c'));
        $this->setDescription('Creates the configuration file');
        $this->addOption(
            self::FORCE_OPTION,
            self::FORCE_OPTION_SHORT,
            InputOption::VALUE_NONE,
            'Overwrite existing configuration file'
        );
    }

    public function executeCommand(IO $io):int {
        $io->writeln($this->getApplication()->getHelp());
        $io->newLine();

        //set configuration location
        $configurationFile = '';
        if ($io->getInput()->getOption('config')) {
            $configurationFile = $io->getInput()->getOption('config');
        } else if ('phar:' === substr(__FILE__, 0, 5)) {
            $configurationFile = str_replace('phar://', '', dirname(__DIR__, 4).'/'.self::FILE_NAME);
        } else {
            $configurationFile = self::FILE_NAME;
        }

        //force option?
        if (!$io->getInput()->getOption(self::FORCE_OPTION) && file_exists($configurationFile)) {
            $io->error(sprintf('The configuration file "%s" already exists.', $configurationFile));
            $io->note('You can force an overwrite with -f or --force option.');
            return 0;
        }

        if(!@copy(self::DEFAULT_FILE, $configurationFile)) {
            throw new IOException(
                sprintf(
                    'Failed to create file "%s": %s.',
                    $configurationFile,
                    error_get_last()['message'],
                ),
                0,
                null,
                $configurationFile,
            );
        } else {
            $io->success(sprintf('Configuration file "%s" created.', $configurationFile));
        }

        return 0;
    }
}

?>