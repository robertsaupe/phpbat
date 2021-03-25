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
use phpseclib3\Net\SFTP as phpSFTP;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\System\SSH\Agent;

class SFTP {

    public function __construct(
        private array $jobs
    ) {
        Logging::Logger()->Trace('job\SFTP');
        if (!is_array($this->jobs)) {
            Logging::Logger()->Error('SFTP: Jobs wrong defined (array needed)');
            return false;
        }
        $i = 0;
        foreach ($this->jobs as $job) {
            Logging::Logger()->Trace('SFTP: Job[' . $i . ']');
            if (!is_array($job)) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] wrong defined (array needed)');
                continue;
            } else if (!isset($job['Source']) || !is_string($job['Source'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] Source not set');
                continue;
            } else if (!isset($job['Destination']) || !is_string($job['Destination'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] Destination not set');
                continue;
            } else if (!isset($job['Server']) || !is_string($job['Server']) || strlen($job['Server']) < 1) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] Server not set');
                continue;
            } else if (!isset($job['User']) || !is_string($job['User']) || strlen($job['User']) < 2) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] User not set');
                continue;
            }
            if (!isset($job['Port']) || !is_int($job['Port'])) $job['Port'] = 22;

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

    private function local_to_remote(array $job, int $i) {
        if (isset($job['Password']) && is_string($job['Password']) && strlen($job['Password']) > 0) {
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            if (!$sftp->login($job['User'], $job['Password'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, wrong password');
                return;
            }
        } else if (isset($job['Key']) && is_string($job['Key']) && strlen($job['Key']) > 0) {
            if (!file_exists($job['Key']) || !is_readable($job['Key'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, key not readable');
                return;
            }
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            $key = PublicKeyLoader::load(file_get_contents($job['Key']));
            if (!$sftp->login($job['User'], $key)) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, wrong key');
                return;
            }
        } else {
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            $agent = new Agent;
            if (!$sftp->login($job['User'], $agent)) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, no password or key set');
                return;
            }
        }

        $rem = array();
        foreach($sftp->nlist($job['Destination'], true) as $item) if ($sftp->is_file($job['Destination'] . '/' . $item)) $rem[] = $item;

        FileMgr::Walker($job['Source'], function($file) use(&$rem, $i, $job, &$sftp) {
            $cleandir = substr($file->dir, strlen($file->basedir) + 1);
            $cleanfile = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $file->fullname;
            $path = $job['Destination'] . '/' . $cleanfile;
            if (in_array($cleanfile, $rem)) {
                //file exists on both
                $size = filesize($file->path);
                $rem_size = $sftp->filesize($path);
                if ($rem_size == $size || $rem_size == -1) {
                    Logging::Logger()->Debug('SFTP: Job[' . $i . '] file already exists, skipped ' . $cleanfile);
                } else {
                    if (@$sftp->put($path, $file->path, phpSFTP::SOURCE_LOCAL_FILE)) {
                        Logging::Logger()->Info('SFTP: Job[' . $i . '] file overwritten ' . $cleanfile);
                    } else {
                        Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t overwritten ' . $cleanfile);
                    }
                }
                unset($rem[array_search($cleanfile, $rem)]);
            } else {
                //copy to server
                @$sftp->mkdir(dirname($path));
                if (@$sftp->put($path, $file->path, phpSFTP::SOURCE_LOCAL_FILE)) {
                    Logging::Logger()->Info('SFTP: Job[' . $i . '] file copied ' . $cleanfile);
                } else {
                    Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t copied ' . $cleanfile);
                }
            }
        });

        //remove other files
        foreach($rem as $file) {
            if (@$sftp->delete($job['Destination'] . '/' . $file)) {
                Logging::Logger()->Info('SFTP: Job[' . $i . '] file deleted ' . $file);
                @$sftp->rmdir(dirname($job['Destination'] . '/' . $file));
            } else {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t deleted ' . $file);
            }
        }

    }

    private function remote_to_local(array $job, int $i) {
        if (isset($job['Password']) && is_string($job['Password']) && strlen($job['Password']) > 0) {
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            if (!$sftp->login($job['User'], $job['Password'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, wrong password');
                return;
            }
        } else if (isset($job['Key']) && is_string($job['Key']) && strlen($job['Key']) > 0) {
            if (!file_exists($job['Key']) || !is_readable($job['Key'])) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, key not readable');
                return;
            }
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            $key = PublicKeyLoader::load(file_get_contents($job['Key']));
            if (!$sftp->login($job['User'], $key)) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, wrong key');
                return;
            }
        } else {
            $sftp = new phpSFTP($job['Server'], $job['Port']);
            $agent = new Agent;
            if (!$sftp->login($job['User'], $agent)) {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] login failed, no password or key set');
                return;
            }
        }

        $rem = array();
        foreach($sftp->nlist($job['Source'], true) as $item) if ($sftp->is_file($job['Source'] . '/' . $item)) $rem[] = $item;

        FileMgr::Walker($job['Destination'], function($file) use(&$rem, $i, $job, &$sftp) {
            $cleandir = substr($file->dir, strlen($file->basedir) + 1);
            $cleanfile = $cleandir . ((strlen($cleandir) > 0) ? '/' : '') . $file->fullname;
            $path = $job['Source'] . '/' . $cleanfile;
            if (in_array($cleanfile, $rem)) {
                //file exists on both
                $size = filesize($file->path);
                $rem_size = $sftp->filesize($path);
                if ($rem_size == $size || $rem_size == -1) {
                    Logging::Logger()->Debug('SFTP: Job[' . $i . '] file already exists, skipped ' . $cleanfile);
                } else {
                    if (@$sftp->get($path, $file->path)) {
                        Logging::Logger()->Info('SFTP: Job[' . $i . '] file overwritten ' . $cleanfile);
                    } else {
                        Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t overwritten ' . $cleanfile);
                    }
                }
                unset($rem[array_search($cleanfile, $rem)]);
            } else {
                //delete local
                if (@unlink($file->path)) {
                    Logging::Logger()->Info('SFTP: Job[' . $i . '] file deleted ' . $cleanfile);
                    @rmdir(dirname($file->path));
                } else {
                    Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t deleted ' . $cleanfile);
                }
            }
        });

        //copy other files
        foreach($rem as $file) {
            @mkdir(dirname($job['Destination'] . '/' . $file));
            if (@$sftp->get($job['Source'] . '/' . $file, $job['Destination'] . '/' . $file)) {
                Logging::Logger()->Info('SFTP: Job[' . $i . '] file copied ' . $file);
            } else {
                Logging::Logger()->Error('SFTP: Job[' . $i . '] file couldn\'t copied ' . $file);
            }
        }

    }

}

?>