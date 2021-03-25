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

use RobertSaupe\phpBAT\Helper\{FileMgr, Time, Logging};
use splitbrain\PHPArchive\{Archive, Tar};

class Backup {

    public function __construct(
        private string $path,
        private array $jobs,
        private ?string $chmod = null,
        private bool $encrypt = false,
        private string $encrypt_cipher = '',
        private string $encrypt_password = ''
    ) {
        Logging::Logger()->Trace('job\Backup');
        $this->path = FileMgr::Dirname($this->path);
        Logging::Logger()->Debug('Backup: Path: ' . $this->path);
        if (!is_array($this->jobs)) {
            Logging::Logger()->Error('Backup: Jobs wrong defined (array needed)');
            return false;
        }
        $i = 0;
        foreach ($this->jobs as $job) {
            Logging::Logger()->Trace('Backup: Job[' . $i . ']');
            if (!is_array($job)) {
                Logging::Logger()->Error('Backup: Job[' . $i . '] wrong defined (array needed)');
                continue;
            } else if (!isset($job['Path']) || !is_string($job['Path']) || !is_dir($job['Path'])) {
                Logging::Logger()->Error('Backup: Job[' . $i . '] Path wrong or not set)');
                continue;
            } else if (!isset($job['Filename']) || !is_string($job['Filename']) || strlen($job['Filename']) < 1) {
                Logging::Logger()->Error('Backup: Job[' . $i . '] Filename wrong or not set)');
                continue;
            }
            if (!isset($job['Excludes']) || !is_array($job['Excludes'])) $job['Excludes'] = array();
            if (!isset($job['Compress']) || !is_bool($job['Compress'])) {
                $job['Compress'] = false;
                Logging::Logger()->Debug('Backup: Job[' . $i . '] Compress disabled');
            } else if (!function_exists("gzopen")) {
                $job['Compress'] = false;
                Logging::Logger()->Warn('Backup: Job[' . $i . '] Compress mode gzip not available (check php zlib)');
            } else {
                Logging::Logger()->Debug('Backup: Job[' . $i . '] Compress mode gzip');
            }
            $this->save($job, $i);
            $i++;
        }
    }

    private function save($job, $i) {
        $tar = new Tar();
        if ($job['Compress'] === true) {
            $ext = '.tar.gz';
            $tar->setCompression(9, Archive::COMPRESS_GZIP);
        } else {
            $ext = '.tar';
            $tar->setCompression(9, Archive::COMPRESS_NONE);
        }
        $file = Time::GetFormattedDate() . '_' . $job['Filename'] . $ext;
        $filepath = $this->path . '/' . $file;
        try {
            $tar->create($filepath);
            FileMgr::Walker($job['Path'], function($file) use(&$tar) {
                if (file_exists($file->path) && is_readable($file->path)) {
                    $tar->addFile($file->path);
                }
            }, true, null, $job['Excludes']);
            $tar->close();
            Logging::Logger()->Info('Backup: Job[' . $i . '] ' . $file . ' saved');
            FileMgr::CHMOD($filepath, $this->chmod);
            new Encrypt($filepath, $this->chmod, $this->encrypt, $this->encrypt_cipher, $this->encrypt_password);
        } catch (\Exception $e) {
            Logging::Logger()->Error('Backup: Job[' . $i . '] ' . $e->getMessage());
        }
    }

}

?>