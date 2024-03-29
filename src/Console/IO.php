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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class IO extends SymfonyStyle {

    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output) {
        parent::__construct($input, $output);
        $this->input = $input;
        $this->output = $output;
    }

    public static function createNull(): self {
        return new self(
            new StringInput(''),
            new NullOutput()
        );
    }

    public function getInput(): InputInterface {
        return $this->input;
    }

    public function isInteractive(): bool {
        return $this->input->isInteractive();
    }

    public function getOutput(): OutputInterface {
        return $this->output;
    }
}

?>