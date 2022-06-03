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

class Color extends BasicCommandApp {

    protected function configure()  {
        $this->setName('color');
        $this->setDescription('Outputs some coloured messages');
    }

    public function executeCommand(IO $io):int {
        $io->caution('caution');
        $io->error('error');
        $io->warning('warning');
        $io->comment('comment');
        $io->success('success');
        $io->info('info');
        $io->note('note');
        $io->section('section');
        $io->text('text');
        $io->title('title');
        $io->block('block');

        $io->newLine();
        $io->writeln('------------');
        $io->newLine();

        $io->writeln("<error>error</error>");
        $io->writeln("<warning>warning</warning>");
        $io->writeln("<comment>comment</comment>");
        $io->writeln("<success>success</success>");
        $io->writeln("<info>info</info>");
        $io->writeln('<note>note</note>');
        $io->writeln('<ignored>ignored</ignored>');
        $io->writeln('<skipped>skipped</skipped>');
        $io->writeln("<code>code</code>");
        $io->writeln('text');

        return 0;
    }
}

?>