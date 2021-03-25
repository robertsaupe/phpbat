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
 * implements a simple logging
 */
class Logging {

    private static ?self $logger = null;

    private array $content = [];

    private bool $live = false;

    private string $live_mode = 'cron';

    private string $file;

    private bool $writeable = true;

    private string $format = 'c';

    private string $ext = 'log';

    /**
     * registered an Logging logger
     *
     * @param string $level
     * @param string $path
     * @return void
     */
    public static function Register(string $level = 'Info', string $path = 'logs', ?string $chmod = null, int $days = 0):void {
        new self($level, $path, $chmod, $days);
    }

    /**
     * get Logging logger
     *
     * @return self
     */
    public static function Logger():self {
        if (self::$logger == null) self::Register();
        return self::$logger;
    }

    public function __construct(
        private string $level = 'Info',
        private string $path = 'logs',
        private ?string $chmod = null,
        private int $days = 0
        ) {
            $this->path = FileMgr::Dirname($this->path);
            $this->file = $this->path . '/' . Time::GetFormattedDate() . '.' . $this->ext;
            self::$logger = $this;
            $this->write_to_file('');
            $this->Trace('Helper\Logging: new logger created with level ' . $level);
            $this->Debug('Logging: Path: ' . $this->path);
            $this->Debug('Logging: write to file: ' . basename($this->file));
            FileMgr::CHMOD($this->file, $this->chmod);
            FileMgr::Delete_Old_Files($this->path, $this->days, array('log'));
    }

    /**
     * write a msg to file
     *
     * @param string $msg
     * @return void
     */
    private function write_to_file(string $msg):void {
        if ($this->writeable) {
            if (@file_put_contents($this->file, $msg, FILE_APPEND | LOCK_EX) === false) {
                $this->writeable = false;
                $this->Error('Logging: logfile ' . $this->file . ' not writeable');
            }
        }
    }

    /**
     * get int level
     *
     * @param string $level
     * @return integer
     */
    private function get_level_int(string $level):int {
        switch ($level) {
            case 'Trace':
                return 5;
                break;

            case 'Debug':
                return 4;
                break;

            case 'Info':
                return 3;
                break;

            case 'Warn':
                return 2;
                break;

            case 'Error':
                return 1;
                break;
            
            default:
            case 'Unknown':
                return 0;
                break;
        }
    }

    /**
     * get string level
     *
     * @param integer $level
     * @return string
     */
    private function get_level_string(int $level):string {
        switch ($level) {
            case 5:
                return 'Trace';
                break;

            case 4:
                return 'Debug';
                break;

            case 3:
                return 'Info';
                break;

            case 2:
                return 'Warn';
                break;

            case 1:
                return 'Error';
                break;
            
            default:
            case 0:
                return 'Unknown';
                break;
        }
    }

    /**
     * msg logic
     *
     * @param string $level
     * @param string $msg
     * @return void
     */
    private function msg(string $level, string $msg):void {
        $content = array("level" => $level, "time" => date($this->format), "msg" => $msg);
        $this->content[] = $content;
        $msg = $this->get_msg($content);
        if ($msg !== "") {
            $this->live($msg);
            $this->write_to_file($msg);
        }
    }

    /**
     * get formatted msg
     *
     * @param array $content
     * @return string
     */
    private function get_msg(array $content, bool $html = false):string {
        if ( $this->get_level_int($this->level) >= $content['level'] ) {
            if ($html == false) {
                return '[' . $this->get_level_string($content['level']) . ']' . ' ' . '[' . $content['time'] . ']' . ' ' . $content['msg'] . "\n";
            } else {
                $level = $this->get_level_string($content['level']);
                $color = '';
                switch ($level) {
                    case 'Trace':
                        $color = '#008027';
                        break;
                        
                    case 'Debug':
                        $color = '#00b9ff';
                        break;
        
                    case 'Info':
                        $color = '#000000';
                        break;
        
                    case 'Warn':
                        $color = '#ff9800';
                        break;
        
                    case 'Error':
                        $color = '#ff0000';
                        break;
                    
                    default:
                    case 'Unknown':
                        $color = '#8d8d8d';
                        break;
                }
                return nl2br('<p style="color: ' . $color . ';">' . '[' . $level . ']' . ' ' . '[' . $content['time'] . ']' . ' ' . htmlentities($content['msg']) . "\n" . '</p>');
            }
        }
        else return "";
    }

    /**
     * logic for live mode
     *
     * @param string $msg
     * @return void
     */
    private function live(string $msg):void {
        if ($this->live && $msg !== "") {
            if ($this->live_mode == 'cli') print($msg);
            else print(nl2br($msg));
        }
    }

    /**
     * set mode to live
     *
     * @param string $mode
     * @return void
     */
    public function Set_Live(string $mode = 'cli'):void {
        $this->Debug('Logging: set output to live mode ' . $mode);
        $this->live = true;
        $this->live_mode = $mode;
        foreach ($this->content as $value) {
            $msg = $this->get_msg($value);
            $this->live($msg);
        }
    }

    /**
     * get live mode
     * available: cli, cron, web
     *
     * @return string
     */
    public function Get_Live_Mode():string {
        return $this->live_mode;
    }

    /**
     * is live mode?
     *
     * @return boolean
     */
    public function Is_Live():bool {
        return $this->live;
    }

    /**
     * get level
     *
     * @return string
     */
    public function Get_Level():string {
        return $this->level;
    }

    /**
     * create msg as Trace
     *
     * @param string $msg
     * @return void
     */
    public function Trace(string $msg):void {
        $this->msg(5, $msg);
    }

    /**
     * create msg as Debug
     *
     * @param string $msg
     * @return void
     */
    public function Debug(string $msg):void {
        $this->msg(4, $msg);
    }

    /**
     * create msg as Info
     *
     * @param string $msg
     * @return void
     */
    public function Info(string $msg):void {
        $this->msg(3, $msg);
    }

    /**
     * create msg as Warn
     *
     * @param string $msg
     * @return void
     */
    public function Warn(string $msg):void {
        $this->msg(2, $msg);
    }

    /**
     * create msg as Error
     *
     * @param string $msg
     * @return void
     */
    public function Error(string $msg):void {
        $this->msg(1, $msg);
    }

    /**
     * get all formatted messages
     *
     * @param null|string $level
     * @return string
     */
    public function Get(?string $level = null, bool $html = false):string {
        if ($level == null) $level = $this->level;
        $log = "";
        foreach ($this->content as $value) {
            $msg = $this->get_msg($value, $html);
            if ($msg !== "") {
                $log .= $msg;
            }
        }
        return $log;
    }

}
?>