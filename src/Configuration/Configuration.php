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
use function function_exists;
use Webmozart\Assert\Assert;
use robertsaupe\phpbat\Configuration\Entry\Logging;
use robertsaupe\phpbat\Configuration\Entry\Mail;

/**
 * @internal
 */
final class Configuration {

    public static function create(string $file, stdClass $jsonObject, string $jsonString): self {

        $timezone = self::retrieveTimeZone($jsonObject);
        self::setTimeZone($timezone);

        return new self(
            $file,
            $jsonObject,
            $jsonString,
            $timezone,
            Logging::create($jsonObject),
            Mail::create($jsonObject)
        );
    }

    private function __construct(
        private string $file,
        private stdClass $jsonObject,
        private string $jsonString,
        private string $timezone,
        private Logging $logging,
        private Mail $mail
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

    private static function retrieveTimeZone(stdClass $jsonObject): string {
        $key = 'timezone';
        $timezone = (isset($jsonObject->{$key}) ? $jsonObject->{$key} : '');
        Assert::string($timezone, 'Must be a string ' . $key);
        return $timezone;
    }

    private static function setTimeZone(string $timezone): bool {
        if (function_exists('date_default_timezone_set') && $timezone !== '' && $timezone !== 'default') return date_default_timezone_set($timezone);
        return false;
    }

    public function getTimeZone(): string {
        return $this->timezone;
    }

    public function getLogging(): Logging {
        return $this->logging;
    }

    public function getMail(): Mail {
        return $this->mail;
    }

}

?>