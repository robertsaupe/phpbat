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

use InvalidArgumentException;
use robertsaupe\phpbat\NotInstantiable;
use robertsaupe\phpbat\Console\IO;
use robertsaupe\phpbat\Exception\NoConfigurationFound;
use robertsaupe\Json\Json;

/**
 * @internal
 */
final class Loader {

    use NotInstantiable;

    private const FILE_NAME = 'phpbat.json';
    private const SCHEMA_FILE = __DIR__.'/../../res/schema.json';

    private static Json $json;

    public static function getConfig(
        ?string $configPath,
        IO $io,
    ): Configuration {
        $configPath = self::getConfigPath($configPath, $io);

        try {
            return self::loadFile($configPath);
        } catch (InvalidArgumentException $invalidConfig) {
            $io->error('The configuration file is invalid.');
            throw $invalidConfig;
        }
    }

    private static function getConfigPath(
        ?string $configPath,
        IO $io,
    ): string {
        try {
            $configPath ??= self::findDefaultPath();
        } catch (NoConfigurationFound $noConfigurationFound) {
            throw $noConfigurationFound;
        }

        $io->comment(
            sprintf(
                'Loading the configuration file "<comment>%s</comment>".',
                $configPath,
            ),
        );

        return $configPath;
    }

    private static function findDefaultPath(): string {
        if (file_exists(self::FILE_NAME)) {
            //Configuration file present in working directory
            return realpath(self::FILE_NAME);
        } else if ('phar:' === substr(__FILE__, 0, 5) && file_exists(str_replace('phar://', '', dirname(__DIR__, 3).'/'.self::FILE_NAME))) {
            //Configuration file present in phar dir
            return realpath(str_replace('phar://', '', dirname(__DIR__, 3).'/'.self::FILE_NAME));
        } else {
            throw new NoConfigurationFound();
        }
    }

    private static function getJson(): Json {
        if (empty(self::$json)) {
            self::$json = new Json();
        }
        return self::$json;
    }

    private static function loadFile(string $file): Configuration {
        $jsonString = self::getJson()->readFile($file);
        $jsonObject = self::getJson()->decode($jsonString);
        self::getJson()->validate(
            $file,
            $jsonObject,
            self::getJson()->decodeFile(self::SCHEMA_FILE),
        );
        return Configuration::create($file, $jsonObject, $jsonString);
    }

}

?>