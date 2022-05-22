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
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\SelfUpdate\Update;
use robertsaupe\phpbat\SelfUpdate\ManifestStrategy;

class SelfUpdate extends BaseCommand {

    private const CHECK_OPTION = 'check';
    private const CHECK_OPTION_SHORT = 'c';

    private const ROLLBACK_OPTION = 'rollback';
    private const ROLLBACK_OPTION_SHORT = 'r';

    private const UNSTABLE_OPTION = 'unstable';
    private const UNSTABLE_OPTION_SHORT = 'u';

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

        $stability = ManifestStrategy::STABLE;
        if ($io->getInput()->getOption(self::UNSTABLE_OPTION)) {
            $stability = ManifestStrategy::ANY;
        }

        $update = new Update($io, $this->getApplication()->getVersion(), $stability);

        if ($io->getInput()->getOption(self::ROLLBACK_OPTION)) {
            $update->rollback();
            return 0;
        }

        if ($io->getInput()->getOption(self::CHECK_OPTION)) {
            $update->printAvailableUpdates();
            return 0;
        }

        $update->doUpdate();

        return 0;
    }

}

?>