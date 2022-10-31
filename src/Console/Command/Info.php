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

use function realpath;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\SystemInfo\Info as siInfo;
use robertsaupe\SystemInfo\OS;

class Info extends BasicCommandConfiguration {

    protected function configure(): void {
        parent::configure();
        $this->setName('info');
        $this->setDescription('Outputs system informations');
    }

    public function executeCommand(IO $io):int {

        $config = $this->getConfig($io);

        $io->writeln(sprintf('Executable: %s', realpath(siInfo::getEnvironment()['server']['PHP_SELF'])));
        $io->writeln(sprintf('User: %s', siInfo::getEnvironment()['user']));
        
        if (OS::getType() == 'Linux') {
            $info = OS::getLinuxInfo();
            if (isset($info) && is_array($info) && isset($info['name'])) {
                $io->writeln(sprintf('OS: %s', $info['name']));
            } else {
                $io->writeln(sprintf('OS: %s', 'Unknown Linux'));
            }
        } else {
            $io->writeln(sprintf('OS: %s', OS::getType()));
        }
        if (function_exists('date_default_timezone_get')) $io->writeln(sprintf('TimeZone: %s', date_default_timezone_get()));
        $io->writeln(sprintf('DateTime: %s', date('c')));
        $io->writeln(sprintf('Logging->Verbosity: %s', $config->getLogging()->getVerbosityKey()));
        $io->writeln(sprintf('Logging->TotalSpace: %s', siInfo::decodeSize(siInfo::getTotalSpace($config->getLogging()->getPath()))));
        $io->writeln(sprintf('Logging->FreeSpace: %s', siInfo::decodeSize(siInfo::getFreeSpace($config->getLogging()->getPath()))));
        $io->writeln(sprintf('Logging->UsedSpace: %s', siInfo::decodeSize(siInfo::getUsedSpace($config->getLogging()->getPath()))));
        $io->writeln(sprintf('Logging->DirectorySize: %s', siInfo::decodeSize(siInfo::getDirectorySize($config->getLogging()->getPath()))));
        $io->writeln(sprintf('PHP->Version: %s', siInfo::getEnvironment()['php']['version']));
        $io->writeln(sprintf('PHP->Extensions: %s', implode(', ', siInfo::getEnvironment()['php']['loaded_extensions'])));

        return 0;
    }
}

?>