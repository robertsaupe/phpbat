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
use Symfony\Component\Console\Input\ArrayInput;

class Configuration extends BasicCommand {

    protected function configure(): void {
        parent::configure();
        $this->setName('configuration');
        $this->setAliases(array('conf', 'cfg'));
        $this->setDescription('configures and validates the configuration file');
    }

    public function executeCommand(IO $io):int {
        $command = $this->getApplication()->find('list');
        $parameters = ['namespace' => 'configuration'];
        $input = new ArrayInput($parameters);
        $command->run($input, $io->getOutput());
        return 0;
    }
}

?>