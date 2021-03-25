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

use RobertSaupe\phpBAT\Helper\{Command, Dependencies, Logging};

class rsync {

    public function __construct(
        private array $jobs
    ) {
        Logging::Logger()->Trace('job\rsync');
        if (!is_array($this->jobs)) {
            Logging::Logger()->Error('rsync: Jobs wrong defined (array needed)');
            return false;
        } else if (!Dependencies::Exec_Available()) {
            Logging::Logger()->Error('rsync: exec not available, check php configuration');
            return false;
        } else if (!Dependencies::Popen_Available()) {
            Logging::Logger()->Error('rsync: popen not available, check php configuration');
            return false;
        } else if (!Dependencies::Program_Available('rsync')){
            Logging::Logger()->Error('rsync: rsync not available');
            return false;
        }
        $i = 0;
        foreach ($this->jobs as $job) {
            Logging::Logger()->Trace('rsync: Job[' . $i . ']');
            if (!is_array($job)) {
                Logging::Logger()->Error('rsync: Job[' . $i . '] wrong defined (array needed)');
                continue;
            } else if (!isset($job['Source']) || !is_string($job['Source'])) {
                Logging::Logger()->Error('rsync: Job[' . $i . '] Source not set');
                continue;
            } else if (!isset($job['Destination']) || !is_string($job['Destination'])) {
                Logging::Logger()->Error('rsync: Job[' . $i . '] Destination not set');
                continue;
            }
            if (!isset($job['Excludes']) || !is_array($job['Excludes'])) $job['Excludes'] = array();
            $job['Exclude'] = '';
            foreach ($job['Excludes'] as $exclude) $job['Exclude'] .= ' --exclude=' . $exclude;
            if (isset($job['Server']) && is_string($job['Server']) && strlen($job['Server']) > 1 && $job['Server'] != 'localhost') {
                //server
                if (!isset($job['User']) || !is_string($job['User']) || strlen($job['User']) < 1) {
                    Logging::Logger()->Error('rsync: Job[' . $i . '] User not set');
                    continue;
                }
                if (!isset($job['SSH']) || !is_bool($job['SSH'])) $job['SSH'] = true;
                $this->server($job, $i);
            } else {
                //local
                $this->local($job, $i);
            }
            $i++;
        }
    }

    private function local($job, $i) {
        $cmd = 'rsync -avxz --delete --delete-excluded' . $job['Exclude'] . ' ' . $job['Source'] . ' ' . $job['Destination'];
        $this->execute($cmd, $i);
    }

    private function server($job, $i) {
        $protocol = ($job['SSH'])?'':'rsync://';
        $sshpass = '';
        $key = '';
        if (isset($job['Password']) && is_string($job['Password']) && strlen($job['Password']) > 0) {
            if (!Dependencies::Program_Available('sshpass')) {
                Logging::Logger()->Error('rsync: Job[' . $i . '] sshpass not available');
                return;
            } else {
                $sshpass = 'sshpass -p "' . $job['Password'] . '" ';
            }
        } else if (isset($job['Key']) && is_string($job['Key']) && strlen($job['Key']) > 0) {
            $key = ' -e "ssh -i ' . $job['Key'] . '"';
        } else {
            Logging::Logger()->Debug('rsync: Job[' . $i . '] try connection without password or specific key');
        }
        if (!isset($job['remote_to_local']) || !is_bool($job['remote_to_local']) || $job['remote_to_local'] != true) {
            //local to remote
            $cmd = $sshpass . 'rsync -avxz' . ' --delete --delete-excluded' . $job['Exclude'] . $key . ' ' . $job['Source'] . ' ' . $protocol . $job['User'] . '@' . $job['Server'] . ':' . $job['Destination'];
            $this->execute($cmd, $i);
        } else {
            //remote to local
            $cmd = $sshpass . 'rsync -avxz' . ' --delete --delete-excluded' . $job['Exclude'] . $key . ' ' . $protocol . $job['User'] . '@' . $job['Server'] . ':' . $job['Source'] . ' ' . $job['Destination'];
            $this->execute($cmd, $i);
        }
    }

    private function execute($cmd, $i) {
        $cmd_secured = preg_replace('/\".*\"/', '"***"', $cmd);
        Logging::Logger()->Debug('rsync: Job[' . $i . '] execute: ' . $cmd_secured);
        $command = new Command($cmd, function($output) use($i) {
            Logging::Logger()->Info('rsync: Job[' . $i . '] ' . $output);
        });
    }

}

?>