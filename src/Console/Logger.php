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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use robertsaupe\Logger\LogFile;
use robertsaupe\Logger\LogMessage;
use robertsaupe\phpbat\Console\IO;

/**
 * @internal
 */
final class Logger extends LogFile {

    public function __construct(
        protected bool $isWriteToFileEnabled,
        protected IO $io,
        protected string $fileBasePath,
        protected string $fileBaseName,
        protected int $chmod = 0600,
        protected string $fileExtension = self::DEFAULT_FILE_EXTENSION,
        protected string $fileDateFormat = self::DEFAULT_FILE_DATE_FORMAT,
        protected string $verbosityKey = self::VERBOSITY_KEY_NORMAL,
        protected string $dateFormat = self::DEFAULT_DATE_FORMAT,
        protected string $messageFormat = self::DEFAULT_MESSAGE_FORMAT
        ) {
            parent::__construct(
                $this->fileBasePath,
                $this->fileBaseName,
                $this->chmod,
                $this->fileExtension,
                $this->fileDateFormat,
                $this->verbosityKey,
                $this->dateFormat,
                $this->messageFormat
            );
    }

    public function error(string $message): LogMessage {
        $this->io->error($message);
        return $this->log($message, self::VERBOSITY_KEY_ERROR);
    }

    public function warning(string $message): LogMessage {
        $this->io->warning($message);
        return $this->log($message, self::VERBOSITY_KEY_WARNING);
    }

    public function info(string $message): LogMessage {
        $this->io->info($message);
        return $this->log($message, self::VERBOSITY_KEY_INFO);
    }

    public function normal(string $message): LogMessage {
        $this->io->writeln($message);
        return $this->log($message, self::VERBOSITY_KEY_NORMAL);
    }

    public function verbose(string $message): LogMessage {
        $this->io->writeln('<verbose>' . $message . '</verbose>', OutputInterface::VERBOSITY_VERBOSE);
        return $this->log($message, self::VERBOSITY_KEY_VERBOSE);
    }

    public function veryverbose(string $message): LogMessage {
        $this->io->writeln('<veryverbose>' . $message . '</veryverbose>', OutputInterface::VERBOSITY_VERY_VERBOSE);
        return $this->log($message, self::VERBOSITY_KEY_VERYVERBOSE);
    }

    public function debug(string $message): LogMessage {
        $this->io->writeln('<debug>' . $message . '</debug>', OutputInterface::VERBOSITY_DEBUG);
        return $this->log($message, self::VERBOSITY_KEY_DEBUG);
    }

    /**
     * @throws IOExceptionInterface 
     */
    protected function mkdir(): void {
        if ($this->isWriteToFileEnabled) {
            parent::mkdir();
        }
    }

    /**
     * @throws IOExceptionInterface 
     */
    protected function chmod(): void {
        if ($this->isWriteToFileEnabled) {
            parent::chmod();
        }
    }

    /**
     * @throws IOExceptionInterface 
     */
    protected function writeToFile(string $content): void {
        if ($this->isWriteToFileEnabled) {
            parent::writeToFile($content);
        }
    }

}

?>