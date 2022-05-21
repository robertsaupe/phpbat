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
use Humbug\SelfUpdate\Updater;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\SelfUpdate\ManifestStrategy;

class SelfUpdate extends BaseCommand {

    private const CHECK_OPTION = 'check';
    private const CHECK_OPTION_SHORT = 'c';

    private const ROLLBACK_OPTION = 'rollback';
    private const ROLLBACK_OPTION_SHORT = 'r';

    private const UNSTABLE_OPTION = 'unstable';
    private const UNSTABLE_OPTION_SHORT = 'u';

    private const MANIFEST_URL = 'https://robertsaupe.github.io/phpbat/release/manifest.json';

    private IO $io;
    private string $version;
    private string $stability;

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
        $io->writeln($this->getApplication()->getHelp());
        $io->newLine();

        $this->io = $io;
        $this->version = $this->getApplication()->getVersion();

        if ($io->getInput()->getOption(self::ROLLBACK_OPTION)) {
            $this->rollback();
            return 0;
        }

        $updater = new Updater(null, false);

        $manifestStrategy = new ManifestStrategy();
        $manifestStrategy->setManifestUrl(self::MANIFEST_URL);
        $manifestStrategy->setCurrentLocalVersion($this->version);
        $manifestStrategy->useSha512();

        if ($io->getInput()->getOption(self::UNSTABLE_OPTION)) {
            $this->stability = ManifestStrategy::ANY;
        } else {
            $this->stability = ManifestStrategy::STABLE;
        }

        $manifestStrategy->setStability($this->stability);

        $updater->setStrategyObject($manifestStrategy);

        if ($io->getInput()->getOption(self::CHECK_OPTION)) {
            $this->printAvailableUpdates($updater);
            return 0;
        }

        $io->writeln('Updating...' . PHP_EOL);

        try {
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
            $this->io->error(sprintf('%s', $e->getMessage()));
        }

        $io->info('You can also select update stability using --unstable or -u');

        return 0;
    }

    private function printAvailableUpdates(Updater $updater) {
        $this->printCurrentLocalVersion();
        $this->printCurrentRemoteVersion($updater);
    }

    private function printCurrentLocalVersion() {
        $this->io->writeln(sprintf(
            'Your current local build version is: <options=bold>%s</options=bold>',
            $this->version
        ));
    }

    private function printCurrentRemoteVersion(Updater $updater) {
        try {
            if ($updater->hasUpdate()) {
                $this->io->writeln(sprintf(
                    'The current %s build available remotely is: <options=bold>%s</options=bold>',
                    $this->stability,
                    $updater->getNewVersion()
                ));
            } elseif (false == $updater->getNewVersion()) {
                $this->io->writeln(sprintf('There are no %s builds available.', $this->stability));
            } else {
                $this->io->writeln(sprintf('You have the current %s build installed.', $this->stability));
            }
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

    private function rollback() {
        $updater = new Updater(null, false);
        try {
            $result = $updater->rollback();
            if ($result) {
                $this->io->success('Application has been rolled back to prior version.');
            } else {
                $this->io->error('Rollback failed for reasons unknown.');
            }
        } catch (\Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

}

?>