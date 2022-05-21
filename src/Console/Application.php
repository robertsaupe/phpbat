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
use function trim;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication {

    public const LOGO = <<<'ASCII'
    
    ██████╗ ██╗  ██╗██████╗ ██████╗  █████╗ ████████╗
    ██╔══██╗██║  ██║██╔══██╗██╔══██╗██╔══██╗╚══██╔══╝
    ██████╔╝███████║██████╔╝██████╔╝███████║   ██║   
    ██╔═══╝ ██╔══██║██╔═══╝ ██╔══██╗██╔══██║   ██║   
    ██║     ██║  ██║██║     ██████╔╝██║  ██║   ██║   
    ╚═╝     ╚═╝  ╚═╝╚═╝     ╚═════╝ ╚═╝  ╚═╝   ╚═╝   

    ASCII;

    public const NAME = 'phpBAT';

    /* Version Specification: https://semver.org/ */
    public const VERSION_MAJOR = 3;
    public const VERSION_MINOR = 0;
    public const VERSION_PATCH = 0;
    public const VERSION_RELEASE = "alpha";//stable|beta|b|RC|alpha|a|patch|pl|p
    public const VERSION_CORE = self::VERSION_MAJOR . '.' . self::VERSION_MINOR . '.' . self::VERSION_PATCH;
    public const VERSION = self::VERSION_CORE . '-' . self::VERSION_RELEASE;

    /* Version replaced by Box */
    public const VERSION_BUILD = '@release-date@';
    public const VERSION_GIT = "@git@";
    public const VERSION_GIT_COMMIT = "@git.commit@";
    public const VERSION_GIT_COMMIT_SHORT = "@git.commit.short@";
    public const VERSION_GIT_TAG = "@git.tag@";
    public const VERSION_GIT_VERSION = "@git.version@";

    private string $version_build;

    public function __construct() {
        $this->version_build = !str_contains(self::VERSION_BUILD, '@') ? self::VERSION_BUILD : '';
        parent::__construct(self::NAME, self::VERSION);
    }

    public function getName(): string {
        return self::NAME;
    }

    public function getVersion(): string {
        return self::VERSION;
    }

    public function getVersionBuild(): string {
        return $this->version_build;
    }

    public function getLongVersion(): string {
        return trim(
            sprintf(
                '<info>%s</info> version <comment>%s</comment> %s',
                $this->getName(),
                $this->getVersion(),
                $this->getVersionBuild(),
            ),
        );
    }

    public function getHelp(): string {
        return self::LOGO.$this->getLongVersion();
    }

    protected function getDefaultCommands(): array {
        $commands = array_merge(
            parent::getDefaultCommands(),
            [
                new Command\Hello(),
                new Command\Color(),
                new Command\Error(),
                new Command\Configuration(),
                new Command\ConfigurationShow(),
                new Command\ConfigurationValidate(),
                new Command\ConfigurationCreate(),
            ],
            ('phar:' === substr(__FILE__, 0, 5)) ? [new Command\SelfUpdate()] : []
        );
        return $commands;
    }

}