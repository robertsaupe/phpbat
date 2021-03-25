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

if (function_exists('error_reporting')) error_reporting(-1);
if (function_exists('ini_set')) ini_set('display_errors', 'E_ALL');
if (PHP_MAJOR_VERSION < 8) die('This app works only with PHP 8.x or higher!');

define('APP_PATH', dirname(__FILE__));

//Library
if (function_exists("chdir")) chdir(APP_PATH);
require_once(APP_PATH . '/Library/Load.php');

//import needed classes
use RobertSaupe\phpBAT\Application;
use RobertSaupe\phpBAT\Helper\{FileMgr};
use splitbrain\PHPArchive\{Archive, Zip, Tar};

//App
$app = new Application(APP_PATH, null, true);

//build path
define('APP_BUILD_PATH', 'builds');
if (!is_dir(APP_BUILD_PATH)) mkdir(APP_BUILD_PATH);

//Version
define('APP_BUILD_VERSION', 'VERSION');
if (file_exists(APP_BUILD_VERSION)) @unlink(APP_BUILD_VERSION);
if (@file_put_contents(APP_BUILD_VERSION, Application::VERSION_CORE)) print('created: ' . APP_BUILD_VERSION . PHP_EOL);

//Archive
define('APP_BUILD_FILENAME', APP_BUILD_PATH . '/' . Application::VERSION_CORE);
define('APP_BUILD_FILEZIP', APP_BUILD_FILENAME . '.zip');
define('APP_BUILD_FILEGZ', APP_BUILD_FILENAME . '.tar.gz');
define('APP_BUILD_FILEZIPSHA1', APP_BUILD_FILEZIP . '.sha1');
define('APP_BUILD_FILEGZSHA1', APP_BUILD_FILEGZ . '.sha1');
if (file_exists(APP_BUILD_FILEZIP)) @unlink(APP_BUILD_FILEZIP);
if (file_exists(APP_BUILD_FILEGZ)) @unlink(APP_BUILD_FILEGZ);
if (file_exists(APP_BUILD_FILEZIPSHA1)) @unlink(APP_BUILD_FILEZIPSHA1);
if (file_exists(APP_BUILD_FILEGZSHA1)) @unlink(APP_BUILD_FILEGZSHA1);

//create
try {
    $zip = new Zip();
    $zip->setCompression(9);
    $zip->create(APP_BUILD_FILEZIP);
    print('created: ' . APP_BUILD_FILEZIP . PHP_EOL);

    $gz = new Tar();
    $gz->setCompression(9, Archive::COMPRESS_GZIP);
    $gz->create(APP_BUILD_FILEGZ);
    print('created: ' . APP_BUILD_FILEGZ . PHP_EOL);

    FileMgr::Walker(APP_PATH, function($file) use (&$zip, &$gz) {
        if (file_exists($file->path) && is_readable($file->path)) {
            $cleandir = substr($file->dir, strlen($file->basedir) + 1);
            $cleanfile = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $file->fullname;
            print('add: ' . $cleanfile . PHP_EOL);
            $zip->addFile($file->path, 'phpbat/' . $cleanfile);
            $gz->addFile($file->path, 'phpbat/' . $cleanfile);
        }
    }, true, null, array('.git', '.gitignore', '.zip', '.log', '.tar', '.gz', '.enc', '.sql', 'builds/', 'buildRelease.php', 'Configuration.jsonc', 'phpBAT.Debug.php'));

    $zip->close();
    print('finished: ' . APP_BUILD_FILEZIP . PHP_EOL);
    if (@file_put_contents(APP_BUILD_FILEZIPSHA1, sha1_file(APP_BUILD_FILEZIP))) print('created: ' . APP_BUILD_FILEZIPSHA1 . PHP_EOL);

    $gz->close();
    print('finished: ' . APP_BUILD_FILEGZ . PHP_EOL);
    if (@file_put_contents(APP_BUILD_FILEGZSHA1, sha1_file(APP_BUILD_FILEGZ))) print('created: ' . APP_BUILD_FILEGZSHA1 . PHP_EOL);

} catch (\Exception $e) {
    print('Error: ' . $e->getMessage()) . PHP_EOL;
}

print('finished' . PHP_EOL);
?>