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

use function sprintf;
use function is_array;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use robertsaupe\Logger\LogFile;
use robertsaupe\Logger\LogMessage;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\Console\Application;
use robertsaupe\SystemInfo\OS;
use robertsaupe\SystemInfo\Info;

/**
 * @internal
 */
final class Logger extends LogFile {

    public function __construct(
        protected Application $application,
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
            $this->infoNoOutput(sprintf('%s version %s %s', $this->application->getName(), $this->application->getVersion(), $this->application->getVersionBuild()));
            $this->infoNoOutput(sprintf('HostName: %s', OS::getHostName()));
            if (OS::getType() == 'Linux') {
                $info = OS::getLinuxInfo();
                if (isset($info) && is_array($info) && isset($info['name'])) {
                    $this->writeNoOutput(sprintf('OS: %s', $info['name']));
                } else {
                    $this->writeNoOutput(sprintf('OS: %s', 'Unknown Linux'));
                }
            } else {
                $this->writeNoOutput(sprintf('OS: %s', OS::getType()));
            }
            $this->verboseNoOutput(sprintf('Logging->Verbosity: %s', $this->verbosityKey));
            $this->verboseNoOutput(sprintf('Logging->TotalSpace: %s', Info::decodeSize(Info::getTotalSpace($this->fileBasePath))));
            $this->writeNoOutput(sprintf('Logging->FreeSpace: %s', Info::decodeSize(Info::getFreeSpace($this->fileBasePath))));
            $this->verboseNoOutput(sprintf('Logging->UsedSpace: %s', Info::decodeSize(Info::getUsedSpace($this->fileBasePath))));
            $this->writeNoOutput(sprintf('Logging->DirectorySize: %s', Info::decodeSize(Info::getDirectorySize($this->fileBasePath))));
    }

    public function error(string $message): LogMessage {
        $this->io->error($message);
        return parent::error($message);
    }

    public function warning(string $message): LogMessage {
        $this->io->warning($message);
        return parent::warning($message);
    }

    public function info(string $message): LogMessage {
        $this->io->info($message);
        return parent::info($message);
    }

    public function normal(string $message): LogMessage {
        $this->io->writeln($message);
        return parent::normal($message);
    }

    public function verbose(string $message): LogMessage {
        $this->io->writeln('<verbose>' . $message . '</verbose>', OutputInterface::VERBOSITY_VERBOSE);
        return parent::verbose($message);
    }

    public function veryverbose(string $message): LogMessage {
        $this->io->writeln('<veryverbose>' . $message . '</veryverbose>', OutputInterface::VERBOSITY_VERY_VERBOSE);
        return parent::veryverbose($message);
    }

    public function debug(string $message): LogMessage {
        $this->io->writeln('<debug>' . $message . '</debug>', OutputInterface::VERBOSITY_DEBUG);
        return parent::debug($message);
    }

    /**
     * same as error, but without console output
     */
    public function errorNoOutput(string $message): LogMessage {
        return parent::error($message);
    }

    /**
     * same as warning, but without console output
     */
    public function warningNoOutput(string $message): LogMessage {
        return parent::warning($message);
    }

    /**
     * same as info, but without console output
     */
    public function infoNoOutput(string $message): LogMessage {
        return parent::info($message);
    }

    /**
     * same as normal, but without console output
     */
    public function normalNoOutput(string $message): LogMessage {
        return parent::normal($message);
    }

    /**
     * same as write, but without console output
     */
    public function writeNoOutput(string $message): LogMessage {
        return parent::normal($message);
    }

    /**
     * same as verbose, but without console output
     */
    public function verboseNoOutput(string $message): LogMessage {
        return parent::verbose($message);
    }

    /**
     * same as veryverbose, but without console output
     */
    public function veryverboseNoOutput(string $message): LogMessage {
        return parent::veryverbose($message);
    }

    /**
     * same as veryveryverbose, but without console output
     */
    public function veryveryverboseNoOutput(string $message): LogMessage {
        return parent::debug($message);
    }

    /**
     * same as debug, but without console output
     */
    public function debugNoOutput(string $message): LogMessage {
        return parent::debug($message);
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