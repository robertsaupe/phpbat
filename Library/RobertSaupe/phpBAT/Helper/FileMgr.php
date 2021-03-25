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
 * implements file and folder functions
 */
class FileMgr {

    /**
     * get Dirname
     *
     * @param string $dir
     * @return string
     */
    public static function Dirname(string $dir):string {
        if (strlen($dir) > 1 && (mb_substr($dir, -1) == '/' || mb_substr($dir, -1) == "\/")) $dir = mb_substr($dir, 0, -1);
        return $dir;
    }

    /**
     * get extension of a file
     *
     * @param string|null $filename
     * @return void
     */
    public static function Get_Extension(?string $filename = null) {
        if(mb_strrpos($filename, '.') !== false) return strtolower(mb_substr($filename, mb_strrpos($filename, '.') + 1));
        else return '';
    }

    /**
     * get filename without extension
     *
     * @param string|null $filename
     * @return void
     */
    public static function Get_Name(?string $filename = null) {
        $undefined = mb_strpos($filename, '?');
        if($undefined !== false) $filename = mb_substr ($filename, 0, $undefined);
        $dot = mb_strrpos($filename, '.');
        if($dot !== false) return mb_substr($filename, 0, $dot);
        else return $filename;
    }

    /**
     * walking a directory
     *
     * @param string $dir
     * @param object $callback
     * @param boolean $recursion
     * @param string|null $basedir
     * @return void
     */
    public static function Walker(string $dir, object $callback, bool $recursion = true, ?string $basedir = null, ?array $excludes = null) {
        $dir = self::Dirname($dir);
        if ($basedir == null) $basedir = $dir;
        if (!is_dir($dir)) return;
        $filelist = scandir($dir);
        foreach($filelist as $file) {
            if($file == '.' || $file == '..') continue;
            $file_path = ($dir == '/' ? '' : $dir) . '/' . $file;

            if ($excludes !== null) {
                foreach ($excludes as $exclude) {
                    if (strlen($exclude) < 2) continue;
                    if ($exclude[0] == '/' && strpos($file_path, $exclude) === 0) continue 2;
                    if ($exclude[0] != '/' && strpos($file_path, $exclude) !== false) continue 2;
                }
            }

            if (is_dir($file_path)) {
                if ($recursion) self::Walker($file_path, $callback, $recursion, $basedir, $excludes);
                continue;
            }
            $file_obj = new \stdClass;
            $file_obj->fullname = $file;
            $file_obj->name = self::Get_Name($file);
            $file_obj->ext = self::Get_Extension($file);
            $file_obj->path = $file_path;
            $file_obj->dir = $dir;
            $file_obj->basedir = $basedir;
            $callback($file_obj);
        }
    }

    /**
     * delete old files
     *
     * @param string $path
     * @param integer $time
     * @param array $exts
     * @return void
     */
    public static function Delete_Old_Files(string $path, int $days, array $exts):void {
        Logging::Logger()->Trace('Helper\FileMgr->Delete_Old_Files: ' . $path . ' ' . $days . ' ' . implode(',', $exts));
        if ($path == '/') {
            Logging::Logger()->Debug('Helper\FileMgr->Delete_Old_Files: deleting old files skipped (root path error)');
            return;
        }
        if (isset($days) && is_int($days) && $days > 0) {
            $path = FileMgr::Dirname($path);
            FileMgr::Walker($path, function($file) use($days, $exts) {
                if (!in_array(strtolower($file->ext), $exts)) return;
                $filemtime = filemtime($file->path);
                $delete_mtime = strtotime('-' . $days . ' days');
                if ($filemtime < $delete_mtime) {
                    if (@unlink($file->path)) Logging::Logger()->Info('Helper\FileMgr->Delete_Old_Files: ' . $file->fullname . ' deleted');
                    else Logging::Logger()->Warn('Helper\FileMgr->Delete_Old_Files: ' . $file->fullname . ' couldn\'t deleted');
                } else {
                    Logging::Logger()->Debug('Helper\FileMgr->Delete_Old_Files: ' . $file->fullname . ' skipped');
                }
            }, false);
        } else {
            Logging::Logger()->Debug('Helper\FileMgr->Delete_Old_Files: deleting old files skipped');
        }
    }

    /**
     * chmod file
     *
     * @param string $file
     * @param array $options
     * @return void
     */
    public static function CHMOD(string $file, ?string $mode = null):void {
        Logging::Logger()->Trace('Helper\FileMgr->CHMOD: ' . $file  . ' ' . $mode);
        if ($mode == null) {
            Logging::Logger()->Debug('Helper\FileMgr->CHMOD: ' . $file  . ' skipped (disabled)');
            return;
        } else if (!is_string($mode) || strlen($mode) != 4) {
            Logging::Logger()->Warn('Helper\FileMgr->CHMOD: ' . $file  . ' couldn\'t changed (mode error)');
            return;
        }else if (!isset($file) || !is_string($file) || !file_exists($file) || !is_readable($file)) {
            Logging::Logger()->Warn('Helper\FileMgr->CHMOD: ' . $file  . ' couldn\'t changed (file error)');
            return;
        } else if (@chmod($file, octdec($mode))) {
            Logging::Logger()->Debug('Helper\FileMgr->CHMOD: ' . $file  . ' changed to ' . $mode);
            return;
        } else {
            Logging::Logger()->Warn('Helper\FileMgr->CHMOD: ' . $file  . ' couldn\'t changed (unknown error)');
            return;
        }
    }

}
?>