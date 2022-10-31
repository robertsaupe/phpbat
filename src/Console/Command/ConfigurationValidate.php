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

use Exception;
use RuntimeException;
use robertsaupe\Json\Exception\JsonValidateException;
use robertsaupe\phpbat\Console\IO;

class ConfigurationValidate extends BasicCommandConfiguration {

    protected function configure(): void {
        parent::configure();
        $this->setName('configuration:validate');
        $this->setAliases(array('conf:validate', 'cfg:validate', 'cfg:v'));
        $this->setDescription('Validates the configuration file');
    }

    public function executeCommand(IO $io):int {

        try {
            $config = $this->getConfig($io);
            $io->success('The configuration file passed the validation.');
        } catch (Exception $exception) {
            if ($io->isVerbose()) {
                throw new RuntimeException(sprintf('The configuration file failed validation: %s', $exception->getMessage()), $exception->getCode(), $exception);
            } else if ($exception instanceof JsonValidateException) {
                $io->error(sprintf('The configuration file failed validation: "%s" does not match the expected JSON schema:', $exception->getValidatedFile()));
                $io->newLine();
                foreach ($exception->getErrors() as $error) {
                    $io->writeln("<comment>  - $error</comment>");
                }
            } else {
                $io->error(sprintf('The configuration file failed validation: %s', $exception->getMessage()));
            }
        }

        return 0;
    }
}

?>