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
 * implements custom json decode with comments support
 */
class JsonUtil {

    /**
     * From https://stackoverflow.com/a/10252511/319266
     * @return array|false|null
     */
    public static function load( $filename ):array|false|null {
        $contents = @file_get_contents( $filename );
        if ( $contents === false ) {
            return false;
        }
        return json_decode( self::stripComments( $contents ), true );
    }

    /**
     * From https://stackoverflow.com/a/10252511/319266
     * @param string $str
     * @return string
     */
    protected static function stripComments( $str ) {
        return preg_replace( '![ \t]*//.*[ \t]*[\r\n]!', '', $str );
    }

}
?>