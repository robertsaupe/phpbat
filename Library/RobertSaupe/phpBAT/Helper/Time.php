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
 * some time functions
 */
class Time {

    public const FORMAT_SHORT = 'Y-m-d';
    public const FORMAT_LONG = 'Y-m-d_H-i-s';

    private static string $format = self::FORMAT_LONG;

    /**
     * set format for date
     *
     * @param string $format
     * @return bool
     */
    public static function SetFormat(string $format):bool {
        if ($format == '') return false;
        self::$format = $format;
        return true;
    }

    /**
     * get format for date
     *
     * @param string $format
     * @return bool
     */
    public static function GetFormat():string {
        return self::$format;
    }

    /**
     * get formatted date
     *
     * @param integer|null $timestamp
     * @return string
     */
    public static function GetFormattedDate(?int $timestamp = null):string {
        if ($timestamp == null) $timestamp = time();
        return date(self::$format, $timestamp);
    }

    /**
     * set timezone
     *
     * @param string|null $timezone
     * @return bool
     */
    public static function SetZone(?string $timezone = null):bool {
        if ( $timezone != null && $timezone != '' ) return date_default_timezone_set($timezone);
        else return false;
    }

    /**
     * get timezone
     *
     * @return string
     */
    public static function GetZone():string {
        return date_default_timezone_get();
    }

}