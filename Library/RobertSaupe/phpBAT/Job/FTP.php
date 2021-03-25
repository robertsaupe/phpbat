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

namespace RobertSaupe\phpBAT\Job;

use RobertSaupe\phpBAT\Helper\{FileMgr, Logging};

class FTP {

    public function __construct(
        private array $jobs
    ) {
        Logging::Logger()->Trace('job\FTP');
        if (!is_array($this->jobs)) {
            Logging::Logger()->Error('FTP: Jobs wrong defined (array needed)');
            return false;
        } else if (!function_exists('ftp_connect')) {
            Logging::Logger()->Error('FTP: ftp not available (check php)');
            return false;
        }
        $i = 0;
        foreach ($this->jobs as $job) {
            Logging::Logger()->Trace('FTP: Job[' . $i . ']');
            if (!is_array($job)) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] wrong defined (array needed)');
                continue;
            } else if (!isset($job['Source']) || !is_string($job['Source'])) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] Source not set');
                continue;
            } else if (!isset($job['Destination']) || !is_string($job['Destination'])) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] Destination not set');
                continue;
            } else if (!isset($job['Server']) || !is_string($job['Server']) || strlen($job['Server']) < 2) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] Server not set');
                continue;
            } else if (!isset($job['User']) || !is_string($job['User']) || strlen($job['User']) < 2) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] User not set');
                continue;
            } else if (!isset($job['Password']) || !is_string($job['Password'])) {
                Logging::Logger()->Error('FTP: Job[' . $i . '] Password not set');
                continue;
            }
            if (!isset($job['Port']) || !is_int($job['Port'])) $job['Port'] = 21;
            if (!isset($job['SSL']) || !is_bool($job['SSL'])) $job['SSL'] = true;

            $job['Source'] = FileMgr::Dirname($job['Source']);
            $job['Destination'] = FileMgr::Dirname($job['Destination']);

            if (!isset($job['remote_to_local']) || !is_bool($job['remote_to_local']) || $job['remote_to_local'] != true) {
                //local to remote
                $this->local_to_remote($job, $i);
            } else {
                //remote to local
                $this->remote_to_local($job, $i);
            }
            $i++;
        }
    }

    private function walk_ftp($conn, string $dir, array $list = array(), ?string $basedir = null) {
        if ($basedir == null) $basedir = $dir;
        $curr = ftp_mlsd($conn, $dir);
        if (!$curr) return $list;
        foreach($curr as $item) {
            if ($item['type'] == 'file') {
                $cleandir = substr($dir, strlen($basedir) + 1);
                $list[] = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $item['name'];
            } else if ($item['type'] == 'dir') {
                $list = $this->walk_ftp($conn, $dir . '/' . $item['name'], $list, $basedir);
            }
        }
        return $list;
    }

    private function local_to_remote(array $job, int $i) {
        if ($job['SSL']) $conn = ftp_ssl_connect($job['Server'], $job['Port']);
        else $conn = ftp_connect($job['Server'], $job['Port']);
        if (!$conn) {
            Logging::Logger()->Error('FTP: Job[' . $i . '] connection failed');
            return;
        }
        if (!@ftp_login($conn, $job['User'], $job['Password'])) {
            Logging::Logger()->Error('FTP: Job[' . $i . '] login failed');
            return;
        }

        $rem = $this->walk_ftp($conn, $job['Destination']);

        FileMgr::Walker($job['Source'], function($file) use(&$rem, $i, $job, &$conn) {
            $cleandir = substr($file->dir, strlen($file->basedir) + 1);
            $cleanfile = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $file->fullname;
            $path = $job['Destination'] . '/' . $cleanfile;
            if (in_array($cleanfile, $rem)) {
                //file exists on both
                $size = filesize($file->path);
                $rem_size = ftp_size($conn, $path);
                if ($rem_size == $size || $rem_size == -1) {
                    Logging::Logger()->Debug('FTP: Job[' . $i . '] file already exists, skipped ' . $cleanfile);
                } else {
                    if (@ftp_put($conn, $path, $file->path)) {
                        Logging::Logger()->Info('FTP: Job[' . $i . '] file overwritten ' . $cleanfile);
                    } else {
                        Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t overwritten ' . $cleanfile);
                    }
                }
                unset($rem[array_search($cleanfile, $rem)]);
            } else {
                //copy to server
                @ftp_mkdir($conn, dirname($path));
                if (@ftp_put($conn, $path, $file->path)) {
                    Logging::Logger()->Info('FTP: Job[' . $i . '] file copied ' . $cleanfile);
                } else {
                    Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t copied ' . $cleanfile);
                }
            }
        });

        //remove other files
        foreach($rem as $file) {
            if (@ftp_delete($conn, $job['Destination'] . '/' . $file)) {
                Logging::Logger()->Info('FTP: Job[' . $i . '] file deleted ' . $file);
                @ftp_rmdir($conn, dirname($job['Destination'] . '/' . $file));
            } else {
                Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t deleted ' . $file);
            }
        }

        ftp_close($conn);
    }

    private function remote_to_local(array $job, int $i) {
        if ($job['SSL']) $conn = ftp_ssl_connect($job['Server'], $job['Port']);
        else $conn = ftp_connect($job['Server'], $job['Port']);
        if (!$conn) {
            Logging::Logger()->Error('FTP: Job[' . $i . '] connection failed');
            return;
        }
        if (!@ftp_login($conn, $job['User'], $job['Password'])) {
            Logging::Logger()->Error('FTP: Job[' . $i . '] login failed');
            return;
        }

        $rem = $this->walk_ftp($conn, $job['Source']);

        FileMgr::Walker($job['Destination'], function($file) use(&$rem, $i, $job, &$conn) {
            $cleandir = substr($file->dir, strlen($file->basedir) + 1);
            $cleanfile = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $file->fullname;
            $path = $job['Source'] . '/' . $cleanfile;
            if (in_array($cleanfile, $rem)) {
                //file exists on both
                $size = filesize($file->path);
                $rem_size = ftp_size($conn, $path);
                if ($rem_size == $size || $rem_size == -1) {
                    Logging::Logger()->Debug('FTP: Job[' . $i . '] file already exists, skipped ' . $cleanfile);
                } else {
                    if (@ftp_get($conn, $file->path, $path)) {
                        Logging::Logger()->Info('FTP: Job[' . $i . '] file overwritten ' . $cleanfile);
                    } else {
                        Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t overwritten ' . $cleanfile);
                    }
                }
                unset($rem[array_search($cleanfile, $rem)]);
            } else {
                //delete local
                if (@unlink($file->path)) {
                    Logging::Logger()->Info('FTP: Job[' . $i . '] file deleted ' . $cleanfile);
                    @rmdir(dirname($file->path));
                } else {
                    Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t deleted ' . $cleanfile);
                }
            }
        });

        //copy other files
        foreach($rem as $file) {
            @mkdir(dirname($job['Destination'] . '/' . $file));
            if (@ftp_get($conn, $job['Destination'] . '/' . $file, $job['Source'] . '/' . $file)) {
                Logging::Logger()->Info('FTP: Job[' . $i . '] file copied ' . $file);
            } else {
                Logging::Logger()->Error('FTP: Job[' . $i . '] file couldn\'t copied ' . $file);
            }
        }

        ftp_close($conn);
    }

}

?>