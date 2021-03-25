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
 * implements OS Detection
 */
class OS {

    public static function Type():string {
        switch (true) {
            case stristr(PHP_OS_FAMILY, 'DAR'): return 'OSX';
            case stristr(PHP_OS_FAMILY, 'WIN'): return 'WIN';
            case stristr(PHP_OS_FAMILY, 'LINUX'): return 'LINUX';
            case stristr(PHP_OS_FAMILY, 'BSD'): return 'BSD';
            case stristr(PHP_OS_FAMILY, 'SOLARIS'): return 'SOLARIS';
            default: return 'UNKNOWN';
        }
    }

    /**
     * returns an array of linux informations from /etc/os-release
     *
     * @return array|null
     * @link https://stackoverflow.com/questions/1482260/how-to-get-the-os-on-which-php-is-running
     */
    public static function Linux_Info():?array {
        if (!function_exists("shell_exec") || !is_readable("/etc/os-release")) return null;

        $os         = shell_exec('cat /etc/os-release');
        $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
        $listIds    = $matchListIds[0];

        $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
        $listVal    = $matchListVal[0];

        array_walk($listIds, function(&$v, $k){
            $v = strtolower(str_replace('=', '', $v));
        });

        array_walk($listVal, function(&$v, $k){
            $v = preg_replace('/=|"/', '', $v);
        });

        return array_combine($listIds, $listVal);
    }

}
?>