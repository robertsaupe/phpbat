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

/**
 * implements autoloader
 */
class Autoloader {

    /**
     * registered an autoloader
     *
     * @param string $path
     * @param string $ext
     * @return void
     */
    public static function Register(string $path, string $ext = '.php'):void {
        new self($path, $ext);
    }

    /**
     * constructor for autoloader
     */
    private function __construct(
        private string $path,
        private string $ext) {
        spl_autoload_register(array($this, 'load'));
    }

    /**
     * load a class
     *
     * @param string $class
     * @return void
     */
    private function load(string $class):void {
        $file = $this->path . '/' . str_replace('\\','/', $class) . $this->ext;
        $file = realpath($file);
        if (file_exists($file) && is_readable($file)) {
            require_once($file);
        }
    }
}
?>