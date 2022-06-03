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
use Symfony\Component\Console\Input\InputOption;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\Phar\SelfUpdate\ManifestUpdate;
use robertsaupe\Phar\SelfUpdate\ManifestStrategy;

class SelfUpdate extends BasicCommandApp {

    private const CHECK_OPTION = 'check';
    private const CHECK_OPTION_SHORT = 'c';

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
            self::CHECK_OPTION_SHORT,
            InputOption::VALUE_NONE,
            'Checks what updates are available'
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

        $stability = ManifestStrategy::STABLE;
        if ($io->getInput()->getOption(self::UNSTABLE_OPTION)) {
            $stability = ManifestStrategy::ANY;
        }

        $updater = new ManifestUpdate($this->getApplication()->getVersion(), self::MANIFEST_URL, stability:$stability);

        if ($io->getInput()->getOption(self::ROLLBACK_OPTION)) {
            $io->writeln('Rollback ...' . PHP_EOL);
            try {
                $result = $updater->rollback();
                if ($result) {
                    $io->success('Application has been rolled back to prior version.');
                } else {
                    $io->error('Rollback failed for reasons unknown.');
                }
            } catch (Exception $e) {
                $io->error(sprintf('%s', $e->getMessage()));
            }
            return 0;
        }

        if ($io->getInput()->getOption(self::CHECK_OPTION)) {
            $io->writeln('Check for Updates ...' . PHP_EOL);
            $io->writeln(sprintf('Your current local build version is: <options=bold>%s</options=bold>', $updater->getCurrentLocalVersion()));
            try {
                $result = $updater->getCurrentRemoteVersion();
                if ($result) {
                    $io->writeln(sprintf('The current %s build available remotely is: <options=bold>%s</options=bold>', $updater->getStability(), $result));
                } else {
                    $io->writeln(sprintf('You have the current %s build installed.', $updater->getStability()));
                }
            } catch (Exception $e) {
                $io->error(sprintf('%s', $e->getMessage()));
            }
            return 0;
        }

        try {
            $io->writeln('Updating ...' . PHP_EOL);
            $result = $updater->update();
            $newVersion = $updater->getNewVersion();
            $oldVersion = $updater->getOldVersion();

            if ($result) {
                $io->success('Application has been updated.');
                $io->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $newVersion
                ));
                $io->writeln(sprintf(
                    '<fg=green>Previous version was:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            } else {
                $io->success('Application is currently up to date.');
                $io->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            }
        } catch (Exception $e) {
            $io->error(sprintf('%s', $e->getMessage()));
        }

        $io->info('You can also select unstable update stability using --unstable or -u');

        return 0;
    }

}

?>