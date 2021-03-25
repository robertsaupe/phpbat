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

if (PHP_MAJOR_VERSION < 8) die('This app works only with PHP 8.x or higher!');

define('LIBRARY_PATH', dirname(__FILE__));

//init autoloader
if (!file_exists(LIBRARY_PATH . '/Autoloader.php') || !is_readable(LIBRARY_PATH . '/Autoloader.php')) {
    die('autoloader couldn\'t loaded!');
} else {
    require_once( LIBRARY_PATH . '/Autoloader.php' );
    Autoloader::Register(LIBRARY_PATH);
}
?>