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

if (function_exists('error_reporting')) error_reporting(0);
if (function_exists('ini_set')) ini_set('display_errors', 'off');
if (PHP_MAJOR_VERSION < 8) die('This app works only with PHP 8.x or higher!');

define('APP_PATH', dirname(__FILE__));

//Library
if (function_exists("chdir")) chdir(APP_PATH);
require_once(APP_PATH . '/Library/Load.php');

//App
use RobertSaupe\phpBAT\Application;
$app = new Application(APP_PATH);
$app->Start();
?>