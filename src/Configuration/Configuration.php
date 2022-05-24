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

namespace robertsaupe\phpbat\Configuration;

use stdClass;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Configuration {

    public static function create(string $file, stdClass $jsonObject, string $jsonString): self {
        $logging = self::retrieveLogging($jsonObject);

        return new self(
            $file,
            $jsonObject,
            $jsonString,
            $logging
        );
    }

    private function __construct(
        private string $file,
        private stdClass $jsonObject,
        private string $jsonString,
        private object $logging
    ) {
        
    }

    public function getConfigurationFile(): string {
        return $this->file;
    }

    public function getJsonObject(): stdClass {
        return $this->jsonObject;
    }

    public function getJsonString(): string {
        return $this->jsonString;
    }

    public function getLoggingEnabled(): bool {
        return $this->logging->{'enabled'};
    }

    public function getLoggingPath(): string {
        return $this->logging->{'path'};
    }

    public function getLoggingVerbosity(): int {
        return $this->logging->{'verbosity'};
    }

    public function getLoggingchmod(): int {
        return $this->logging->{'chmod'};
    }

    private static function retrieveLogging(stdClass $jsonObject): object {
        $key = 'logging';
        Assert::notNull($jsonObject->{$key}, 'Cannot retrieve '.$key);
        $key_enabled = 'enabled';
        Assert::notNull($jsonObject->{$key}->{$key_enabled}, 'Cannot retrieve '.$key.'.'.$key_enabled);
        Assert::boolean($jsonObject->{$key}->{$key_enabled}, 'Must be a boolean '.$key.'.'.$key_enabled);
        $key_path = 'path';
        Assert::notNull($jsonObject->{$key}->{$key_path}, 'Cannot retrieve '.$key.'.'.$key_path);
        $jsonObject->{$key}->{$key_path} = trim($jsonObject->{$key}->{$key_path});
        Assert::notEmpty($jsonObject->{$key}->{$key_path}, 'Cannot be empty '.$key.'.'.$key_path);
        $key_verbosity = 'verbosity';
        Assert::notNull($jsonObject->{$key}->{$key_verbosity}, 'Cannot retrieve '.$key.'.'.$key_verbosity);
        Assert::integer($jsonObject->{$key}->{$key_verbosity}, 'Must be a number '.$key.'.'.$key_verbosity);
        $key_chmod = 'chmod';
        if (!isset($jsonObject->{$key}->{$key_chmod})) $jsonObject->{$key}->{$key_chmod} = '0600';
        $jsonObject->{$key}->{$key_chmod} = trim($jsonObject->{$key}->{$key_chmod});
        Assert::notEmpty($jsonObject->{$key}->{$key_chmod}, 'Cannot be empty '.$key.'.'.$key_chmod);
        $jsonObject->{$key}->{$key_chmod} = octdec($jsonObject->{$key}->{$key_chmod});
        return $jsonObject->{$key};
    }

}

?>