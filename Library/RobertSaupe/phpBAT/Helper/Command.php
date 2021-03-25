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
 * implements command features
 * @link https://stackoverflow.com/questions/20107147/php-reading-shell-exec-live-output
 */
class Command {

    private array $output;

    public function __construct(
        private string $cmd,
        private object $callback
        ) {
            while (@ ob_end_flush());
            $handle = popen("$this->cmd 2>&1 ; echo Exit status : $?", 'r');
            $live_output     = "";
            $complete_output = "";
            while (!feof($handle)) {
                $live_output = fread($handle, 4096);
                $live_output = rtrim($live_output);
                $live_output_arr = explode(PHP_EOL, $live_output);
                foreach ($live_output_arr as $output) {
                    if (isset($output) && $output != "") {
                        $complete_output = $complete_output . $output . "\n";
                        call_user_func($this->callback, $output);
                    }
                }
                @flush();
            }
            pclose($handle);
            preg_match('/[0-9]+$/', $complete_output, $matches);
            $this->output = array (
                'status'  => intval($matches[0]),
                'output'       => rtrim(str_replace("Exit status : " . $matches[0], '', $complete_output))
            );
    }

    public function Success():bool {
        if (isset($this->output) && is_array($this->output) && isset($this->output['status']) && $this->output['status'] === 0) return true;
        else return false;
    }

    public function Output():?string {
        if (isset($this->output) && is_array($this->output) && isset($this->output['output']) && is_string($this->output['output'])) return $this->output['output'];
        else return null;
    }

    public function Status():?int {
        if (isset($this->output) && is_array($this->output) && isset($this->output['status']) && is_int($this->output['status'])) return $this->output['status'];
        else return null;
    }

}
?>