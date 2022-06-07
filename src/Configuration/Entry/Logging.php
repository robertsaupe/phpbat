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

namespace robertsaupe\phpbat\Configuration\Entry;

use stdClass;
use InvalidArgumentException;
use Webmozart\Assert\Assert;
use robertsaupe\phpbat\Console\Logger;

/**
 * @internal
 */
final class Logging {

    public static function create(stdClass $jsonObject): self {

        $key = 'logging';
        $object = (isset($jsonObject->{$key}) ? $jsonObject->{$key} : new stdClass());
        //Assert::notNull($object, 'Cannot retrieve ' . $key);

        $keyEnabled = 'enabled';
        $objectEnabled = (isset($object->{$keyEnabled}) ? $object->{$keyEnabled} : true);
        Assert::boolean($objectEnabled, 'Must be a boolean ' .$key . '.' . $keyEnabled);

        $keySendMail = 'sendMail';
        $objectSendMail = (isset($object->{$keySendMail}) ? $object->{$keySendMail} : false);
        Assert::boolean($objectSendMail, 'Must be a boolean ' .$key . '.' . $keySendMail);

        $keyVerbosity = 'verbosity';
        $objectVerbosity = (isset($object->{$keyVerbosity}) ? $object->{$keyVerbosity} : Logger::VERBOSITY_KEY_NORMAL);
        Assert::string($objectVerbosity, 'Must be a string ' . $key . '.' . $keyVerbosity);
        if (!Logger::isVerbosityKeyValid($objectVerbosity)) throw new InvalidArgumentException('Must be a valid string ' . $key . '.' . $keyVerbosity);

        $keyDateFormat = 'dateFormat';
        $objectDateFormat = (isset($object->{$keyDateFormat}) ? $object->{$keyDateFormat} : Logger::DEFAULT_DATE_FORMAT);
        Assert::string($objectDateFormat, 'Must be a string ' . $key . '.' . $keyDateFormat);
        $objectDateFormat = trim($objectDateFormat);
        Assert::notEmpty($objectDateFormat, 'Cannot be empty ' . $key . '.' . $keyDateFormat);

        $keyMessageFormat = 'messageFormat';
        $objectMessageFormat = (isset($object->{$keyMessageFormat}) ? $object->{$keyMessageFormat} : Logger::DEFAULT_MESSAGE_FORMAT);
        Assert::string($objectMessageFormat, 'Must be a string ' . $key . '.' . $keyMessageFormat);
        Assert::notEmpty($objectMessageFormat, 'Cannot be empty ' . $key . '.' . $keyMessageFormat);

        $keyPath = 'path';
        $objectPath = (isset($object->{$keyPath}) ? $object->{$keyPath} : 'logs');
        Assert::string($objectPath, 'Must be a string ' . $key . '.' . $keyPath);
        $objectPath = trim($objectPath);
        Assert::notEmpty($objectPath, 'Cannot be empty ' . $key . '.' . $keyPath);

        $keyFileDateFormat = 'fileDateFormat';
        $objectFileDateFormat = (isset($object->{$keyFileDateFormat}) ? $object->{$keyFileDateFormat} : Logger::DEFAULT_FILE_DATE_FORMAT);
        Assert::string($objectFileDateFormat, 'Must be a string ' . $key . '.' . $keyFileDateFormat);
        $keyFileDateFormat = trim($objectFileDateFormat);
        Assert::notEmpty($objectFileDateFormat, 'Cannot be empty ' . $key . '.' . $keyFileDateFormat);

        $keyChmod = 'chmod';
        $objectChmod = (isset($object->{$keyChmod}) ? $object->{$keyChmod} : '0600');
        $objectChmod = trim($objectChmod);
        Assert::notEmpty($objectChmod, 'Cannot be empty ' . $key . '.' . $keyChmod);
        $objectChmod = octdec($objectChmod);

        return new self(
            $objectEnabled,
            $objectSendMail,
            $objectVerbosity,
            $objectDateFormat,
            $objectMessageFormat,
            $objectPath,
            $objectFileDateFormat,
            $objectChmod
        );
    }

    private function __construct(
        private bool $enabled,
        private bool $sendMail,
        private string $verbosityKey,
        private string $dateFormat,
        private string $messageFormat,
        private string $path,
        private string $fileDateFormat,
        private int|float $chmod
    ) {
        
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function getSendMail(): bool {
        return $this->sendMail;
    }

    public function getVerbosityKey(): string {
        return $this->verbosityKey;
    }

    public function getDateFormat(): string {
        return $this->dateFormat;
    }

    public function getMessageFormat(): string {
        return $this->messageFormat;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getFileDateFormat(): string {
        return $this->fileDateFormat;
    }

    public function getChmod(): int|float {
        return $this->chmod;
    }

}

?>