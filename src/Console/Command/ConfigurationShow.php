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

class ConfigurationShow extends BasicCommandConfiguration {

    protected function configure(): void {
        parent::configure();
        $this->setName('configuration:show');
        $this->setAliases(array('conf:show', 'cfg:show', 'cfg:s'));
        $this->setDescription('Shows the configuration (useful to debug failures)');
    }

    public function executeCommand(IO $io):int {
        $config = $this->getConfig($io);

        $io->title('Configuration');
        $io->writeln(sprintf('<code>%s</code>', print_r($config, true)));
        $io->newLine();

        return 0;
    }
}

?>