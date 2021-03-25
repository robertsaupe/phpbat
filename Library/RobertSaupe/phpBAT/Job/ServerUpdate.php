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

use RobertSaupe\phpBAT\Helper\{Command, OS, Dependencies, Logging};

class ServerUpdate {

    public function __construct(
        private array $job
    ) {
        Logging::Logger()->Trace('job\ServerUpdate');
        if (!is_array($this->job)) {
            Logging::Logger()->Error('ServerUpdate: Job wrong defined (array needed)');
            return false;
        } else if (!isset($this->job['Enabled']) || !is_bool($this->job['Enabled']) || $this->job['Enabled'] == false) {
            Logging::Logger()->Debug('ServerUpdate: skippped (disabled)');
            return false;
        } else if (OS::Type() !== 'LINUX') {
            Logging::Logger()->Error('ServerUpdate: only available on Linux');
            return false;
        } else if (!Dependencies::Exec_Available()) {
            Logging::Logger()->Error('ServerUpdate: exec not available, check php configuration');
            return false;
        } else if (!Dependencies::Popen_Available()) {
            Logging::Logger()->Error('ServerUpdate: popen not available, check php configuration');
            return false;
        } else if (Dependencies::ProcessUser() != 'root') {
            Logging::Logger()->Error('ServerUpdate: need root privileges');
            return false;
        }
        if (!isset($this->job['Cleanup']) || !is_bool($this->job['Cleanup']) || $this->job['Cleanup'] == false) {
            $this->job['Cleanup'] = false;
        }
        $os = OS::Linux_Info();
        if (!is_array($os) || !isset($os['id']) || !isset($os['name'])) {
            Logging::Logger()->Error('ServerUpdate: /etc/os-release not readable');
            return false;
        }
        switch ($os['id']) {
            case 'raspbian':
            case 'debian':
            case 'ubuntu':
                $this->update_deb();
                if ($this->job['Cleanup'] == true) $this->cleanup_deb();
                break;

            case 'manjaro':
            case 'arch':
                $this->update_arch();
                if ($this->job['Cleanup'] == true) $this->cleanup_arch();
                break;
            
            default:
                Logging::Logger()->Error('ServerUpdate: OS (' . $os['name'] . ') is currently not supported ');
                return false;
                break;
        }
    }

    private function update_deb() {
        $cmd = 'apt-get -y -qq update';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
        $cmd = 'apt-get -y -f upgrade';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
        $cmd = 'apt-get -y -f dist-upgrade';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
    }

    private function cleanup_deb() {
        if (!Dependencies::Program_Available('deborphan')) {
            Logging::Logger()->Warn('ServerUpdate: deborphan required');
        } else {
            $cmd = 'if [ -n "$(deborphan)" ]; then apt-get -y --purge remove `deborphan`; fi';
            Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
            $command = new Command($cmd, function($output) {
                Logging::Logger()->Info('ServerUpdate: ' . $output);
            });
        }
        $cmd = 'apt-get -y autoremove';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
        $cmd = 'apt-get -y autoclean';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
    }

    private function update_arch() {
        $cmd = 'pacman -Syyuu --noconfirm';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
    }

    private function cleanup_arch() {
        $cmd = 'pacman -Rs $(pacman -Qdtq) --noconfirm';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
        $cmd = 'paccache -rvk3';
        Logging::Logger()->Debug('ServerUpdate: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerUpdate: ' . $output);
        });
    }

}

?>