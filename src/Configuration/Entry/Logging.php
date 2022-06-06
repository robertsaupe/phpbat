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
use robertsaupe\phpbat\Console\Logger as ConsoleLogger;

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

        $keyPath = 'path';
        $objectPath = (isset($object->{$keyPath}) ? $object->{$keyPath} : 'logs');
        Assert::string($objectPath, 'Must be a string ' . $key . '.' . $keyPath);
        $objectPath = trim($objectPath);
        Assert::notEmpty($objectPath, 'Cannot be empty ' . $key . '.' . $keyPath);

        $keyVerbosity = 'verbosity';
        $objectVerbosity = (isset($object->{$keyVerbosity}) ? $object->{$keyVerbosity} : ConsoleLogger::VERBOSITY_KEY_NORMAL);
        Assert::string($objectVerbosity, 'Must be a string ' . $key . '.' . $keyVerbosity);
        if (!ConsoleLogger::isVerbosityKeyValid($objectVerbosity)) throw new InvalidArgumentException('Must be a valid string ' . $key . '.' . $keyVerbosity);

        $keyChmod = 'chmod';
        $objectChmod = (isset($object->{$keyChmod}) ? $object->{$keyChmod} : '0600');
        $objectChmod = trim($objectChmod);
        Assert::notEmpty($objectChmod, 'Cannot be empty ' . $key . '.' . $keyChmod);
        $objectChmod = octdec($objectChmod);

        return new self(
            $objectEnabled,
            $objectPath,
            $objectVerbosity,
            $objectChmod
        );
    }

    private function __construct(
        private bool $enabled,
        private string $path,
        private string $verbosityKey,
        private int|float $chmod
    ) {
        
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getVerbosityKey(): string {
        return $this->verbosityKey;
    }

    public function getChmod(): int|float {
        return $this->chmod;
    }

}

?>