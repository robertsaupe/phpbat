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

use function sprintf;
use Exception;
use Symfony\Component\Console\Input\InputOption;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\Phar\SelfUpdate\ManifestUpdate;
use robertsaupe\Phar\SelfUpdate\ManifestStrategy;
use robertsaupe\phpbat\Console\Logger;
use robertsaupe\phpbat\Console\Mailer;

class SelfUpdate extends BasicCommandConfiguration {

    private const CHECK_OPTION = 'check';

    private const NOLOG_OPTION = 'no-log';
    private const NOMAIL_OPTION = 'no-mail';

    private const ROLLBACK_OPTION = 'rollback';
    private const ROLLBACK_OPTION_SHORT = 'r';

    private const UNSTABLE_OPTION = 'unstable';
    private const UNSTABLE_OPTION_SHORT = 'u';

    private const MANIFEST_URL = 'https://robertsaupe.github.io/phpbat/release/manifest.json';

    protected function configure(): void {
        parent::configure();
        $this->setName('selfupdate');
        $this->setAliases(array('self-update'));
        $this->setDescription('Update to most recent build. Default: stable');
        $this->addOption(
            self::CHECK_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Checks what updates are available'
        );
        $this->addOption(
            self::NOLOG_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Disable logging for this update'
        );
        $this->addOption(
            self::NOMAIL_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Disable send mail for this update'
        );
        $this->addOption(
            self::ROLLBACK_OPTION,
            self::ROLLBACK_OPTION_SHORT,
            InputOption::VALUE_NONE,
            'Revert to previous build, if available on filesystem.'
        );
        $this->addOption(
            self::UNSTABLE_OPTION,
            self::UNSTABLE_OPTION_SHORT,
            InputOption::VALUE_NONE,
            'Update to most recent build across all possible stability tracks.'
        );
    }

    public function executeCommand(IO $io):int {
        $config = $this->getConfig($io);

        $logger = new Logger(
            /** @phpstan-ignore-next-line */
            application: $this->getApplication(),
            io: $io,
            isWriteToFileEnabled: $io->getInput()->getOption(self::NOLOG_OPTION) ? false : $config->getLogging()->getEnabled(),
            verbosityKey: $config->getLogging()->getVerbosityKey(),
            dateFormat: $config->getLogging()->getDateFormat(),
            messageFormat: $config->getLogging()->getMessageFormat(),
            fileBasePath: $config->getLogging()->getPath(),
            fileBaseName: 'update',
            fileDateFormat: $config->getLogging()->getFileDateFormat(),
            chmod: $config->getLogging()->getChmod()
        );

        $logger->verbose('SelfUpdate started');

        if ($io->getInput()->getOption(self::UNSTABLE_OPTION)) {
            $stability = ManifestStrategy::ANY;
            $logger->verbose('use stability: unstable');
        } else {
            $stability = ManifestStrategy::STABLE;
            $logger->verbose('use stability: stable');
        }

        $updater = new ManifestUpdate($this->getApplication()->getVersion(), self::MANIFEST_URL, stability:$stability);

        if ($io->getInput()->getOption(self::ROLLBACK_OPTION)) {
            $logger->write('Rollback ...');
            try {
                $result = $updater->rollback();
                if ($result) {
                    $logger->info('Application has been rolled back to prior version.');
                } else {
                    $logger->error('Rollback failed for reasons unknown.');
                }
            } catch (Exception $e) {
                $logger->error(sprintf('%s', $e->getMessage()));
            }
            return 0;
        }

        if ($io->getInput()->getOption(self::CHECK_OPTION)) {
            $logger->write('Check for Updates ...');
            $logger->verbose(sprintf('Your current local build version is: %s', $updater->getCurrentLocalVersion()));
            try {
                $result = $updater->getCurrentRemoteVersion();
                if ($result) {
                    $logger->info(sprintf('The current %s build available remotely is: %s', $updater->getStability(), $result));
                } else {
                    $logger->info(sprintf('You have the current %s build installed.', $updater->getStability()));
                }
            } catch (Exception $e) {
                $logger->error(sprintf('%s', $e->getMessage()));
            }
            return 0;
        }

        try {
            $logger->write('Updating ...');
            $result = $updater->update();
            $newVersion = $updater->getNewVersion();
            $oldVersion = $updater->getOldVersion();

            if ($result) {
                $logger->info('Application has been updated.');
                $logger->write(sprintf('Current version is: %s.', $newVersion));
                $logger->write(sprintf('Previous version was: %s.', $oldVersion));
            } else {
                $logger->info('Application is currently up to date.');
                $logger->write(sprintf('Current version is: %s.', $oldVersion));
            }
        } catch (Exception $e) {
            $logger->error(sprintf('%s', $e->getMessage()));
        }

        $io->info('You can also select unstable update stability using --unstable or -u');

        if (!$io->getInput()->getOption(self::NOMAIL_OPTION)) {
            $mailer = new Mailer(
                /** @phpstan-ignore-next-line */
                application: $this->getApplication(),
                isMailSendEnabled: $config->getLogging()->getSendMail(),
                mailConfig: $config->getMail(),
                logger: $logger
            );
        }

        return 0;
    }

}

?>