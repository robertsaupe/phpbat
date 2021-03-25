<?php
/**
 * phpBAT
 * 
 * Please report bugs on https://github.com/robertsaupe/phpbat/issues
 *
 * @author Robert Saupe <mail@robertsaupe.de>
 * @copyright Copyright (c) 2018, Robert Saupe. All rights reserved
 * @link https://github.com/robertsaupe/phpbat
 * @license MIT License
 */

namespace RobertSaupe\phpBAT\Helper;

/**
 * implements check fo dependencies
 */
class Dependencies {

    public static function CLI():bool {
        if ( defined('STDIN') ) {
            return true;
        } else if ( php_sapi_name() === 'cli' ) {
            return true;
        } else if ( array_key_exists('SHELL', $_ENV) ) {
            return true;
        } else if ( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
            return true;
        } else if ( !array_key_exists('REQUEST_METHOD', $_SERVER) ) {
            return true;
        }
        return false;
    }

    public static function Exec_Available():bool {
        if ( function_exists('exec') && strpos(@ini_get('disable_functions'),'exec') === false && @exec('echo EXEC') == 'EXEC') return true;
        else return false;
    }

    public static function Popen_Available():bool {
        if ( function_exists('popen') && strpos(@ini_get('disable_functions'),'popen') === false) return true;
        else return false;
    }

    public static function Program_Available($name):bool {
        if (@exec('command -v ' . $name . ' >/dev/null 2>&1 || { echo >&1 "false";}') == 'false') return false;
        else return true;
    }

    public static function Extension_Available($extension):bool {
        return extension_loaded($extension);
    }

    public static function ProcessUser():?string {
        $processUser = posix_getpwuid(posix_geteuid());
        if (isset($processUser['name'])) return $processUser['name'];
        else null;
    }

}
?>