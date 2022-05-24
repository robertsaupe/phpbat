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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use robertsaupe\phpbat\Console\IO;

/**
 * @internal
 */
final class Logger {

    public const VERBOSITY_QUIT = 0;
    public const VERBOSITY_ERROR = 8;
    public const VERBOSITY_WARN = 16;
    public const VERBOSITY_INFO = 32;
    public const VERBOSITY_NORMAL = 64;
    public const VERBOSITY_VERBOSE = 128;
    public const VERBOSITY_VERY_VERBOSE = 256;
    public const VERBOSITY_DEBUG = 512;

    private const DATE_FORMAT = 'c';

    private const FILE_EXT = 'log';
    private const FILE_DATE_FORMAT = 'Y-m-d_H-i-s';

    private Filesystem $filesystem;
    private string $file = '';
    private bool $isWritable = true;
    private array $messages = [];

    public function __construct(
        private bool $enabled,
        private string $path,
        private string $basename,
        private int $verbosity,
        private int $chmod,
        private readonly IO $io
        ) {
        $this->filesystem = new Filesystem();
        $this->path = trim($this->path);
        $this->path = Path::canonicalize($this->path);
        $this->basename = trim($this->basename);
        $this->file = $this->path . '/' . date(self::FILE_DATE_FORMAT) . '_' . $this->basename . '.' . self::FILE_EXT;
        $this->mkdir();
        $this->writeToFile('');
        $this->chmod();
    }

    public function log(int $verbosity, string $message): void {
        $message_array = [
            "verbosity" => $verbosity,
            "time" => date(self::DATE_FORMAT),
            "message" => $message
        ];
        $this->messages[] = $message_array;
        $this->writeMessageToFile($message_array);
    }

    public function error(string $message): void {
        $this->log(self::VERBOSITY_ERROR, $message);
        $this->io->error($message);
    }

    public function warning(string $message): void {
        $this->log(self::VERBOSITY_WARN, $message);
        $this->io->warning($message);
    }

    public function info(string $message): void {
        $this->log(self::VERBOSITY_INFO, $message);
        $this->io->info($message);
    }

    public function write(string $message): void {
        $this->log(self::VERBOSITY_NORMAL, $message);
        $this->io->writeln($message);
    }

    public function verbose(string $message): void {
        $this->log(self::VERBOSITY_VERBOSE, $message);
        $this->io->writeln('<verbose>' . $message . '</verbose>', OutputInterface::VERBOSITY_VERBOSE);
    }

    public function very_verbose(string $message): void {
        $this->log(self::VERBOSITY_VERY_VERBOSE, $message);
        $this->io->writeln('<veryverbose>' . $message . '</veryverbose>', OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    public function debug(string $message): void {
        $this->log(self::VERBOSITY_DEBUG, $message);
        $this->io->writeln('<debug>' . $message . '</debug>', OutputInterface::VERBOSITY_DEBUG);
    }

    private function convertVerbosity(int $verbosity): string {
        switch ($verbosity) {
            case self::VERBOSITY_ERROR:
                return 'Error';
                break;
            case self::VERBOSITY_WARN:
                return 'Warning';
                break;
            case self::VERBOSITY_INFO:
                return 'Info';
                break;
            case self::VERBOSITY_NORMAL:
                return 'Normal';
                break;
            case self::VERBOSITY_VERBOSE:
                return 'Verbose';
                break;
            case self::VERBOSITY_VERY_VERBOSE:
                return 'Very-Verbose';
                break;
            case self::VERBOSITY_DEBUG:
                return 'Debug';
                break;
            default:
                return 'Unknown';
                break;
        }
    }

    private function mkdir(): void {
        try{
            $this->filesystem->mkdir($this->path);
        } catch (IOExceptionInterface $exception) {
            $this->isWritable = false;
            $this->io->warning(sprintf('Failed to create directory at "%s"', $exception->getPath()));
        }
    }

    private function chmod(): void {
        try{
            $this->filesystem->chmod($this->file, $this->chmod);
        } catch (IOExceptionInterface $exception) {
            $this->isWritable = false;
            $this->io->warning(sprintf('Failed to chmod file at "%s"', $exception->getPath()));
        }
    }

    private function writeToFile(string $content): void {
        if ($this->isWritable && $this->enabled) {
            try{
                $this->filesystem->appendToFile($this->file, $content);
            } catch (IOExceptionInterface $exception) {
                $this->isWritable = false;
                $this->io->warning(sprintf('Failed to create or append file at "%s"', $exception->getPath()));
            }
        }
    }

    private function writeMessageToFile(array $message): void {
        if ($this->verbosity >= $message['verbosity']) {
            $this->writeToFile($this->getFormattedMessage($message));
        }
    }

    private function getFormattedMessage(array $message, bool $isHTML = false): string {
        if ($isHTML) {
            $font_color = '#5f5f5f';
            $background_color = 'none';
            switch ($message['verbosity']) {
                case self::VERBOSITY_ERROR:
                    $font_color =  '#ff0000';
                    break;
                case self::VERBOSITY_WARN:
                    $font_color =  '#ff8600';
                    break;
                case self::VERBOSITY_INFO:
                    $font_color =  '#008000';
                    break;
                case self::VERBOSITY_NORMAL:
                    $font_color =  '#000000';
                    break;
                case self::VERBOSITY_VERBOSE:
                    $font_color =  '#767676';
                    $background_color = '#ffd99a';
                    break;
                case self::VERBOSITY_VERY_VERBOSE:
                    $font_color =  '#4e4e4e';
                    $background_color = '#ffd99a';
                    break;
                case self::VERBOSITY_DEBUG:
                    $font_color =  '#000000';
                    $background_color = '#ffd99a';
                    break;
                default:
                    $font_color =  '#5f5f5f';
                    break;
            }
            return nl2br('<p style="color:' . $font_color . ';background-color:' . $background_color . ';">' . '[' . $this->convertVerbosity($message['verbosity']) . ']' . ' ' . '[' . $message['time'] . ']' . ' ' . htmlentities($message['message']) . '</p>' . PHP_EOL);
        } else {
            return '[' . $this->convertVerbosity($message['verbosity']) . ']' . ' ' . '[' . $message['time'] . ']' . ' ' . $message['message'] . PHP_EOL;
        }
    }

    public function getAllFormattedMessagesByVerbosity(bool $isHTML = false, ?int $verbosity = null): string {
        if ($verbosity === null) $verbosity = $this->verbosity;
        $messages = '';
        foreach ($this->messages as $message) {
            if ($verbosity >= $message['verbosity']) {
                $messages .= $this->getFormattedMessage($message, $isHTML);
            }
        }
        return $messages;
    }

}

?>