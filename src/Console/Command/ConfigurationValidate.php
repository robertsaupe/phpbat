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
use robertsaupe\phpbat\Exception\JsonValidateException;
use robertsaupe\phpbat\Console\IO;

class ConfigurationValidate extends ConfigurationBaseCommand {

    protected function configure(): void {
        parent::configure();
        $this->setName('configuration:validate');
        $this->setAliases(array('conf:validate', 'cfg:validate', 'cfg:v'));
        $this->setDescription('Validates the configuration file');
    }

    public function executeCommand(IO $io):int {
        $io->writeln($this->getApplication()->getHelp());
        $io->newLine();

        try {
            $config = $this->getConfig($io);
            $io->success('The configuration file passed the validation.');
        } catch (Exception $exception) {
            if ($io->isVerbose()) {
                throw new RuntimeException(
                    sprintf(
                        'The configuration file failed validation: %s',
                        $exception->getMessage(),
                    ),
                    $exception->getCode(),
                    $exception,
                );
            }
            if ($exception instanceof JsonValidateException) {
                $io->writeln(
                    sprintf(
                        '<error>The configuration file failed validation: "%s" does not match the expected JSON schema:</error>',
                        $exception->getValidatedFile(),
                    ),
                );
                $io->newLine();
                foreach ($exception->getErrors() as $error) {
                    $io->writeln("<comment>  - $error</comment>");
                }
            } else {
                $errorMessage = isset($exception)
                    ? sprintf('The configuration file failed validation: %s', $exception->getMessage())
                    : 'The configuration file failed validation.'
                ;
                $io->writeln(
                    sprintf(
                        '<error>%s</error>',
                        $errorMessage,
                    ),
                );
            }
        }

        return 0;
    }
}

?>