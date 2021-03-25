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

use RobertSaupe\phpBAT\Helper\{Command, FileMgr, OS, Time, Dependencies, Logging};

class ServerBackup {

    public function __construct(
        private string $path,
        private array $job,
        private ?string $chmod = null,
        private bool $encrypt = false,
        private string $encrypt_cipher = '',
        private string $encrypt_password = ''
    ) {
        Logging::Logger()->Trace('job\ServerBackup');
        $this->path = FileMgr::Dirname($this->path);
        Logging::Logger()->Debug('ServerBackup: Path: ' . $this->path);
        if (!is_array($this->job)) {
            Logging::Logger()->Error('ServerBackup: Job wrong defined (array needed)');
            return false;
        } else if (!isset($this->job['Enabled']) || !is_bool($this->job['Enabled']) || $this->job['Enabled'] == false) {
            Logging::Logger()->Debug('ServerBackup: skippped (disabled)');
            return false;
        } else if (!isset($this->job['Filename']) || !is_string($this->job['Filename']) || strlen($this->job['Filename']) < 1) {
            Logging::Logger()->Error('ServerBackup: Filename not set or no string');
            return false;
        } else if (!isset($this->job['Excludes']) || !is_array($this->job['Excludes'])) {
            Logging::Logger()->Error('ServerBackup: Excludes not set or no array');
            return false;
        } else if (OS::Type() !== 'LINUX') {
            Logging::Logger()->Error('ServerBackup: only available on Linux');
            return false;
        } else if (!Dependencies::Exec_Available()) {
            Logging::Logger()->Error('ServerBackup: exec not available, check php configuration');
            return false;
        } else if (!Dependencies::Popen_Available()) {
            Logging::Logger()->Error('ServerBackup: popen not available, check php configuration');
            return false;
        } else if (!Dependencies::Program_Available('tar')) {
            Logging::Logger()->Error('ServerBackup: tar not available');
            return false;
        } else if (Dependencies::ProcessUser() != 'root') {
            Logging::Logger()->Error('ServerBackup: need root privileges');
            return false;
        }
        if (!isset($this->job['Compress']) || !is_bool($this->job['Compress'])) {
            $this->job['Compress'] = false;
        }
        $os = OS::Linux_Info();
        if (!is_array($os) || !isset($os['id']) || !isset($os['name'])) {
            Logging::Logger()->Error('ServerBackup: /etc/os-release not readable');
            return false;
        }
        switch ($os['id']) {
            case 'raspbian':
            case 'debian':
            case 'ubuntu':
                $this->save();
                break;

            case 'arch':
            case 'manjaro':
                $this->save();
                break;
            
            default:
                Logging::Logger()->Error('ServerBackup: OS (' . $os['name'] . ') is currently not supported ');
                return false;
                break;
        }
    }

    private function save() {
        if ($this->job['Compress'] == true) {
            $ext = '.tar.gz';
            $tar_flags = 'zcpf';
        } else {
            $ext = '.tar';
            $tar_flags = 'cpf';
        }
        $file = Time::GetFormattedDate() . '_' . $this->job['Filename'] . $ext;
        $filepath = $this->path . '/' . $file;
        $stm_excludes =array(
            '*~'
            ,'/proc/*'
            ,'/dev/*'
            ,'/sys/*'
            ,'/run/*'
            ,'/boot/efi/*'
            ,'/media/*'
            ,'/tmp/*'
            ,'/var/tmp/*'
            ,'/var/run/*'
            ,'/var/lock/*'
            ,'/var/spool/postfix/*'
            ,'/var/cache/pacman/pkg/*'
            ,'/lib/init/rw/*'
            ,'cache/*'
            ,'.cache/*'
            ,'lost+found/*'
            ,$this->path . '/*'
        );
        $this->job['Excludes'] = array_merge($this->job['Excludes'], $stm_excludes);
        $this->job['Exclude'] = '';
        foreach ($this->job['Excludes'] as $exclude) $this->job['Exclude'] .= ' --exclude=' . $exclude;
        $cmd = 'tar ' . $tar_flags . ' ' . $filepath . $this->job['Exclude'] . ' /';
        Logging::Logger()->Debug('ServerBackup: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Debug('ServerBackup: ' . $output);
        });
        if (file_exists($filepath)) {
            Logging::Logger()->Info('ServerBackup: ' . $file . ' saved');
            FileMgr::CHMOD($filepath, $this->chmod);
            new Encrypt($filepath, $this->chmod, $this->encrypt, $this->encrypt_cipher, $this->encrypt_password);
        } else {
            Logging::Logger()->Error('ServerBackup: ' . $file . ' coudn\'t saved');
        }
    }

}

?>