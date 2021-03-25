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

use RobertSaupe\phpBAT\Helper\{Dependencies, FileMgr, Time, Logging};
use Ifsnop\Mysqldump as IMysqldump;

class MySQLDump {

    public function __construct(
        private string $path,
        private array $jobs,
        private ?string $chmod = null,
        private bool $encrypt = false,
        private string $encrypt_cipher = '',
        private string $encrypt_password = ''
    ) {
        Logging::Logger()->Trace('job\MySQLDump');
        $this->path = FileMgr::Dirname($this->path);
        Logging::Logger()->Debug('MySQLDump: Path: ' . $this->path);
        if (!is_array($this->jobs)) {
            Logging::Logger()->Error('MySQLDump: Jobs wrong defined (array needed)');
            return false;
        } else if (!Dependencies::Extension_Available('pdo_mysql')) {
            Logging::Logger()->Error('MySQLDump: pdo_mysql extension not available (check php)');
            return false;
        }
        $i = 0;
        foreach ($this->jobs as $job) {
            Logging::Logger()->Trace('MySQLDump: Job[' . $i . ']');
            if (!is_array($job)) {
                Logging::Logger()->Error('MySQLDump: Job[' . $i . '] wrong defined (array needed)');
                continue;
            } else if (!isset($job['Host']) || !is_string($job['Host']) || strlen($job['Host']) < 3) {
                Logging::Logger()->Error('MySQLDump: Job[' . $i . '] Host wrong or not set)');
                continue;
            } else if (!isset($job['DB']) || !is_string($job['DB']) || strlen($job['DB']) < 1) {
                Logging::Logger()->Error('MySQLDump: Job[' . $i . '] DB wrong or not set)');
                continue;
            } else if (!isset($job['User']) || !is_string($job['User']) || strlen($job['User']) < 1) {
                Logging::Logger()->Error('MySQLDump: Job[' . $i . '] User wrong or not set)');
                continue;
            }
            if (!isset($job['Port']) || !is_string($job['Port']) || !is_int($job['Port'])) $job['Port'] = '3306';
            if (!isset($job['Password']) || !is_string($job['Password'])) $job['Password'] = '';
            if (!isset($job['Compress']) || !is_bool($job['Compress']) || $job['Compress'] !== true) {
                $job['Compress'] = false;
                Logging::Logger()->Debug('MySQLDump: Job[' . $i . '] Compress disabled');
            } else if (!function_exists("gzopen")) {
                $job['Compress'] = false;
                Logging::Logger()->Warn('MySQLDump: Job[' . $i . '] Compress mode gzip not available (check php zlib)');
            } else {
                Logging::Logger()->Debug('MySQLDump: Job[' . $i . '] Compress mode gzip');
            }
            $this->dump($job, $i);
            $i++;
        }
    }

    private function dump($job, $i) {
        $dumpSettings = ($job['Compress'] ? array('compress' => IMysqldump\Mysqldump::GZIP) : array('compress' => IMysqldump\Mysqldump::NONE));
        $ext = ($job['Compress'] ? '.sql.gz' : '.sql');
        $filename = (isset($job['Filename']) && is_string($job['Filename']) && strlen($job['Filename']) > 0 ? $job['Filename'] : $job['DB']);
        $file = Time::GetFormattedDate() . '_' . $filename . $ext;
        try {
            $filepath = $this->path . '/' . $file;
            $dump = new IMysqldump\Mysqldump('mysql:host=' . $job['Host'] . ':' . $job['Port'] . ';dbname=' . $job['DB'], $job['User'], $job['Password'], $dumpSettings);
            $dump->start($filepath);
            Logging::Logger()->Info('MySQLDump: Job[' . $i . '] file ' . $file . ' saved');
            FileMgr::CHMOD($filepath, $this->chmod);
            new Encrypt($filepath, $this->chmod, $this->encrypt, $this->encrypt_cipher, $this->encrypt_password);
        } catch (\Exception $e) {
            Logging::Logger()->Error('MySQLDump: Job[' . $i . '] ' . $e->getMessage());
        }
    }

}

?>