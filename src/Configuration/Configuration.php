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

    private const TEST_KEY = 'test';

    public static function create(string $file, stdClass $jsonObject, string $jsonString): self {
        $test = self::retrieveTest($jsonObject);

        return new self(
            $file,
            $jsonObject,
            $jsonString,
            $test
        );
    }

    private function __construct(
        private string $file,
        private stdClass $jsonObject,
        private string $jsonString,
        private string $test
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

    public function getTest(): string {
        return $this->test;
    }

    private static function retrieveTest(stdClass $jsonObject): string {
        Assert::notNull($jsonObject->{self::TEST_KEY}, 'Cannot retrieve test: no test configured.');
        $test = trim($jsonObject->{self::TEST_KEY});
        Assert::notEmpty($test, 'test cannot be empty.');
        return $test;
    }

}

?>