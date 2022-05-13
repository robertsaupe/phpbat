<?php
/**
 * phpBAT
 *
 * @category   backup
 * @package    phpBAT
 * @copyright  Copyright (c) 2018 Robert Saupe <mail@robertsaupe.de> (https://robertsaupe.de)
 * @license    https://raw.githubusercontent.com/robertsaupe/phpbat/master/LICENSE MIT License
 */

namespace phpbat\Console;

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
    public const VERSION_MAJOR = "@version.major@";
    public const VERSION_MINOR = "@version.minor@";
    public const VERSION_PATCH = "@version.patch@";
    public const VERSION_CORE = self::VERSION_MAJOR . '.' . self::VERSION_MINOR . '.' . self::VERSION_PATCH;
    public const VERSION_RELEASE = "@version.release@";//stable, beta, alpha, develop, unstable
    public const VERSION_BUILD = '@release-date@';
    public const VERSION = self::VERSION_CORE . '-' . self::VERSION_RELEASE . '+' . self::VERSION_BUILD;

    /* Version GIT */
    public const VERSION_GIT = "@git@";
    public const VERSION_GIT_COMMIT = "@git.commit@";
    public const VERSION_GIT_COMMIT_SHORT = "@git.commit.short@";
    public const VERSION_GIT_TAG = "@git.tag@";
    public const VERSION_GIT_VERSION = "@git.version@";

    public function __construct() {
        // $pharVersion = '@git.version@';
        // if ($pharVersion !== '@'.'git.version'.'@') {
        //     parent::__construct(self::LOGO.PHP_EOL.self::NAME, $pharVersion);
        //     return;
        // }
        parent::__construct(self::LOGO.PHP_EOL.self::NAME, self::VERSION);
    }

}