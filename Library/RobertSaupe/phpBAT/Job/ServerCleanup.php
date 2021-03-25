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

class ServerCleanup {

    public function __construct() {
        Logging::Logger()->Trace('job\ServerCleanup');
        if (OS::Type() !== 'LINUX') {
            Logging::Logger()->Error('ServerCleanup: only available on Linux');
            return false;
        } else if (!Dependencies::Exec_Available()) {
            Logging::Logger()->Error('ServerCleanup: exec not available, check php configuration');
            return false;
        } else if (!Dependencies::Popen_Available()) {
            Logging::Logger()->Error('ServerCleanup: popen not available, check php configuration');
            return false;
        } else if (Dependencies::ProcessUser() != 'root') {
            Logging::Logger()->Error('ServerCleanup: need root privileges');
            return false;
        }
        $os = OS::Linux_Info();
        if (!is_array($os) || !isset($os['id']) || !isset($os['name'])) {
            Logging::Logger()->Error('ServerCleanup: /etc/os-release not readable');
            return false;
        }
        switch ($os['id']) {
            case 'raspbian':
            case 'debian':
            case 'ubuntu':
            case 'manjaro':
            case 'arch':
                $this->log();
                $this->journal();
                break;
            
            default:
                Logging::Logger()->Error('ServerCleanup: OS (' . $os['name'] . ') is currently not supported ');
                return false;
                break;
        }
    }

    private function log() {
        $cmd = 'find /var/log -type f -name "*.gz" -o -name "*.old" -print0 | xargs -0 -r rm -v';
        Logging::Logger()->Debug('ServerCleanup: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerCleanup: ' . $output);
        });
    }

    private function journal() {
        if (!Dependencies::Program_Available('journalctl')) return;
        $cmd = 'journalctl --vacuum-time=10d';
        Logging::Logger()->Debug('ServerCleanup: execute: ' . $cmd);
        $command = new Command($cmd, function($output) {
            Logging::Logger()->Info('ServerCleanup: ' . $output);
        });
    }

}

?>