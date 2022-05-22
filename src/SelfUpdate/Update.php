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

namespace robertsaupe\phpbat\SelfUpdate;

use Exception;
use Humbug\SelfUpdate\Updater;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\SelfUpdate\ManifestStrategy;

class Update {

    private const MANIFEST_URL = 'https://robertsaupe.github.io/phpbat/release/manifest.json';

    private Updater $updater;

    public function __construct(
        private IO $io,
        private string $version,
        private string $stability
        ) {
            $this->updater = new Updater(null, false);

            $manifestStrategy = new ManifestStrategy();
            $manifestStrategy->setManifestUrl(self::MANIFEST_URL);
            $manifestStrategy->setCurrentLocalVersion($this->version);
            $manifestStrategy->useSha512();
    
            $manifestStrategy->setStability($this->stability);
    
            $this->updater->setStrategyObject($manifestStrategy);
    }

    public function rollback(): void {
        try {
            $result = $this->updater->rollback();
            if ($result) {
                $this->io->success('Application has been rolled back to prior version.');
            } else {
                $this->io->error('Rollback failed for reasons unknown.');
            }
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

    public function hasUpdate(): bool {
        try {
            return $this->updater->hasUpdate();
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

    public function hasUpdateVersion(): string|false {
        try {
            if ($this->updater->hasUpdate()) {
                return $this->updater->getNewVersion();
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

    public function printAvailableUpdates(): void {
        $this->printCurrentLocalVersion();
        $this->printCurrentRemoteVersion();
    }

    private function printCurrentLocalVersion() {
        $this->io->writeln(sprintf(
            'Your current local build version is: <options=bold>%s</options=bold>',
            $this->version
        ));
    }

    private function printCurrentRemoteVersion(): void {
        try {
            if ($this->updater->hasUpdate()) {
                $this->io->writeln(sprintf(
                    'The current %s build available remotely is: <options=bold>%s</options=bold>',
                    $this->stability,
                    $this->updater->getNewVersion()
                ));
            } else if (false == $this->updater->getNewVersion()) {
                $this->io->writeln(sprintf('There are no %s builds available.', $this->stability));
            } else {
                $this->io->writeln(sprintf('You have the current %s build installed.', $this->stability));
            }
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }
    }

    public function doUpdate(): void {
        $this->io->writeln('Updating...' . PHP_EOL);

        try {
            $result = $this->updater->update();

            $newVersion = $this->updater->getNewVersion();
            $oldVersion = $this->updater->getOldVersion();

            if ($result) {
                $this->io->success('Application has been updated.');
                $this->io->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $newVersion
                ));
                $this->io->writeln(sprintf(
                    '<fg=green>Previous version was:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            } else {
                $this->io->success('Application is currently up to date.');
                $this->io->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            }
        } catch (Exception $e) {
            $this->io->error(sprintf('%s', $e->getMessage()));
        }

        $this->io->info('You can also select update stability using --unstable or -u');
    }

}

?>