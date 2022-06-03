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
use robertsaupe\phpbat\Configuration\Entry\Logging;

/**
 * @internal
 */
final class Configuration {

    public static function create(string $file, stdClass $jsonObject, string $jsonString): self {
        return new self(
            $file,
            $jsonObject,
            $jsonString,
            Logging::create($jsonObject)
        );
    }

    private function __construct(
        private string $file,
        private stdClass $jsonObject,
        private string $jsonString,
        private Logging $logging
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

    public function getLogging(): Logging {
        return $this->logging;
    }

}

?>