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

namespace robertsaupe\phpbat\Console;

use robertsaupe\phpbat\NotInstantiable;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class OutputFormatterStyleConfigurator {

    use NotInstantiable;

    public static function configure(OutputInterface $output): void {
        $outputFormatter = $output->getFormatter();

        $outputFormatter->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $outputFormatter->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
        $outputFormatter->setStyle('comment', new OutputFormatterStyle('yellow'));
        $outputFormatter->setStyle('success', new OutputFormatterStyle('black', 'green'));
        $outputFormatter->setStyle('info', new OutputFormatterStyle('green'));
        $outputFormatter->setStyle('note', new OutputFormatterStyle('blue'));
        $outputFormatter->setStyle('ignored', new OutputFormatterStyle('gray'));
        $outputFormatter->setStyle('skipped', new OutputFormatterStyle('magenta'));
        $outputFormatter->setStyle('code', new OutputFormatterStyle('white'));
    }
}

?>