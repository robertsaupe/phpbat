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

namespace RobertSaupe\phpBAT;

//import needed classes
use RobertSaupe\phpBAT\Helper\{Dependencies, Encryption, FileMgr, JsonUtil, Logging, OS, SelfUpdate, Time, Mail};
use RobertSaupe\phpBAT\Job\{Backup, FTP, MySQLDump, rsync, ServerBackup, ServerCleanup, ServerUpdate, SFTP};

class Application {

    public const NAME = 'phpBAT';
    public const DESCRIPTION = 'a PHP based Backup & Admin Tool';
    public const FULLNAME = self::NAME . ' - ' . self::DESCRIPTION;
    public const URL = 'https://github.com/robertsaupe/phpbat';
    public const DEVELOPER = 'Robert Saupe';
    public const DEVELOPER_URL = 'https://robertsaupe.de';
    public const DEVELOPER_MAIL = 'mail@robertsaupe.de';

    /* Version Specification: https://semver.org/ */
    public const VERSION_MAJOR = 2;
    public const VERSION_MINOR = 0;
    public const VERSION_PATCH = 0;
    public const VERSION_CORE = self::VERSION_MAJOR . '.' . self::VERSION_MINOR . '.' . self::VERSION_PATCH;
    public const VERSION_RELEASE = 'stable';
    public const VERSION_BUILD = '2021-03-25';
    public const VERSION = self::VERSION_CORE . '-' . self::VERSION_RELEASE . '+' . self::VERSION_BUILD;

    private array|false|null $configuration = null;

    public function __construct(
        private ?string $path = null,
        private ?string $configuration_file = null,
        private bool $debug = false
        ) {
            if ($this->path == null) $this->path = dirname($_SERVER["PHP_SELF"]);
            $this->load_Configuration();
        }

    private function load_Configuration() {
        if ($this->configuration_file == null) $this->configuration_file = $this->path . '/Configuration.jsonc';
        if (!file_exists($this->configuration_file) || !is_readable($this->configuration_file)) {
            die('Configuration couldn\'t loaded!' . PHP_EOL . 'Please copy Configuration.Default.jsonc to Configuration.jsonc and editing it.' . PHP_EOL);
        } else {
            $this->configuration = JsonUtil::load($this->configuration_file);
            $this->read_Configuration();
        }
    }

    private function read_Configuration() {

        if ($this->configuration === null || $this->configuration === false) {
            print('Configuration couldn\'t readed!' . PHP_EOL);
            die(json_last_error_msg() . PHP_EOL);
        }
        else if (!isset($this->configuration['phpBAT'])) die('Wrong Configuration!' . PHP_EOL);
        $this->configuration = $this->configuration['phpBAT'];

        //Time
        if (isset($this->configuration['Timezone']) && is_string($this->configuration['Timezone']) && $this->configuration['Timezone'] != '') Time::SetZone($this->configuration['Timezone']);
        if (isset($this->configuration['Timeshort']) && $this->configuration['Timeshort'] === true) Time::SetFormat(Time::FORMAT_SHORT);

        //Backup
        if (!isset($this->configuration['Backup'])) $this->configuration['Backup'] = array();
        if (!isset($this->configuration['Backup']['Path'])) $this->configuration['Backup']['Path'] = 'backups';
        if (!is_dir($this->configuration['Backup']['Path'])) mkdir($this->configuration['Backup']['Path']);
        if (!isset($this->configuration['Backup']['chmod'])) $this->configuration['Backup']['chmod'] = null;
        if (!isset($this->configuration['Backup']['Days'])) $this->configuration['Backup']['Days'] = 0;

        //Encryption
        if (!isset($this->configuration['Backup']['Encrypt'])) $this->configuration['Backup']['Encrypt'] = array();
        if (!isset($this->configuration['Backup']['Encrypt']['Enabled'])) $this->configuration['Backup']['Encrypt']['Enabled'] = false;
        if (!isset($this->configuration['Backup']['Encrypt']['Cipher'])) $this->configuration['Backup']['Encrypt']['Cipher'] = 'aes-256-cbc';
        if (!isset($this->configuration['Backup']['Encrypt']['Password'])) $this->configuration['Backup']['Encrypt']['Password'] = 'password';

        //Decryption
        if (!isset($this->configuration['Backup']['Decrypt'])) $this->configuration['Backup']['Decrypt'] = array();
        if (!isset($this->configuration['Backup']['Decrypt']['Path'])) $this->configuration['Backup']['Decrypt']['Path'] = 'decrypted';
        if (!is_dir($this->configuration['Backup']['Decrypt']['Path'])) mkdir($this->configuration['Backup']['Decrypt']['Path']);

        //Logs
        if (!isset($this->configuration['Logging'])) $this->configuration['Logging'] = array();
        if (!isset($this->configuration['Logging']['Level'])) $this->configuration['Logging']['Level'] = 'Info';
        if (!isset($this->configuration['Logging']['Path'])) $this->configuration['Logging']['Path'] = 'logs';
        if (!isset($this->configuration['Logging']['chmod'])) $this->configuration['Logging']['chmod'] = null;
        if (!isset($this->configuration['Logging']['Days'])) $this->configuration['Logging']['Days'] = 0;
        if (!is_dir($this->configuration['Logging']['Path'])) mkdir($this->configuration['Logging']['Path']);

        //SelfUpdate
        if (!isset($this->configuration['SelfUpdate'])) $this->configuration['SelfUpdate'] = true;
        if (!isset($this->configuration['SelfUpdateDebug'])) $this->configuration['SelfUpdateDebug'] = false;

        //Jobs
        if (!isset($this->configuration['Jobs'])) $this->configuration['Jobs'] = array();
    }

    public function Start() {

        //init Logging
        Logging::Register($this->configuration['Logging']['Level'], $this->configuration['Logging']['Path'], $this->configuration['Logging']['chmod'], $this->configuration['Logging']['Days']);

        //check mode
        if (Dependencies::CLI()) {
            $options = getopt("c", array("cron"));
            if (array_key_exists('c', $options) || array_key_exists('cron', $options)) {
                //cron
                if (isset($_ENV["PATH"])) putenv("PATH=" .$_ENV["PATH"]. ':/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
                else putenv("PATH=" . ':/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
            }
            else {
                //cli
                Logging::Logger()->Set_Live();
            }
        } else {
            //web
            Logging::Logger()->Set_Live('web');
            Logging::Logger()->Warn('This should not accessible on public!');
        }

        //logging some basic informations about this app
        Logging::Logger()->Info(self::FULLNAME);
        Logging::Logger()->Info('Version: ' . self::VERSION);
        Logging::Logger()->Info('Timezone: ' . Time::GetZone());
        Logging::Logger()->Info('Loglevel: ' . Logging::Logger()->Get_Level());
        Logging::Logger()->Info('Server: ' . php_uname('n'));
        if (OS::Type() == 'LINUX') {
            $os = OS::Linux_Info();
            if (is_array($os) && isset($os['name'])) Logging::Logger()->Info('OS: ' . $os['name']);
            else Logging::Logger()->Info('OS: ' . 'Unknown Linux');
        } else {
            Logging::Logger()->Info('OS: ' . OS::Type());
        }

        //check arguments for decrypt
        if (Dependencies::CLI()) {
            $options = getopt("df:", array("decrypt", "file:"));
            if (array_key_exists('d', $options) || array_key_exists('decrypt', $options)) {
                $file = null;
                if (array_key_exists('f', $options)) $file = $options['f'];
                else if (array_key_exists('file', $options)) $file = $options['file'];
                if ($file == null) {
                    Logging::Logger()->Info('Decrypt: ' . 'no file specified, walk to all backup files!');
                    FileMgr::Walker($this->configuration['Backup']['Path'], function($file) {
                        if (!in_array(strtolower($file->ext), ['enc'])) return;
                        Logging::Logger()->Info('Decrypt: ' . 'file ' . $file->fullname);
                        Encryption::Decrypt_File($file->path, $this->configuration['Backup']['Encrypt']['Cipher'], $this->configuration['Backup']['Encrypt']['Password'], FileMgr::Dirname($this->configuration['Backup']['Decrypt']['Path']) . '/' . basename(substr($file->path, 0, -4)));
                    }, false);
                } else if (!file_exists($file)|| !is_readable($file)) {
                    Logging::Logger()->Error('Decrypt: ' . 'file ' . $file . ' not exists!');
                } else {
                    Logging::Logger()->Info('Decrypt: ' . 'file ' . $file);
                    Encryption::Decrypt_File($file, $this->configuration['Backup']['Encrypt']['Cipher'], $this->configuration['Backup']['Encrypt']['Password'], FileMgr::Dirname($this->configuration['Backup']['Decrypt']['Path']) . '/' . basename(substr($file, 0, -4)));
                }
                Logging::Logger()->Info('Decrypt: ' . 'Finished');
                die();
            }
        }

        //update
        new SelfUpdate($this->configuration['SelfUpdate'], self::VERSION_CORE);

        //delete old backup files
        FileMgr::Delete_Old_Files($this->configuration['Backup']['Path'], $this->configuration['Backup']['Days'], array('tar', 'gz', 'sql', 'enc'));

        //jobs
        if (isset($this->configuration['Jobs']['MySQLDump'])) new MySQLDump($this->configuration['Backup']['Path'], $this->configuration['Jobs']['MySQLDump'], $this->configuration['Backup']['chmod'], $this->configuration['Backup']['Encrypt']['Enabled'], $this->configuration['Backup']['Encrypt']['Cipher'], $this->configuration['Backup']['Encrypt']['Password']);
        if (isset($this->configuration['Jobs']['Backup'])) new Backup($this->configuration['Backup']['Path'], $this->configuration['Jobs']['Backup'], $this->configuration['Backup']['chmod'], $this->configuration['Backup']['Encrypt']['Enabled'], $this->configuration['Backup']['Encrypt']['Cipher'], $this->configuration['Backup']['Encrypt']['Password']);
        if (isset($this->configuration['Jobs']['ServerBackup'])) new ServerBackup($this->configuration['Backup']['Path'], $this->configuration['Jobs']['ServerBackup'], $this->configuration['Backup']['chmod'], $this->configuration['Backup']['Encrypt']['Enabled'], $this->configuration['Backup']['Encrypt']['Cipher'], $this->configuration['Backup']['Encrypt']['Password']);

        if (isset($this->configuration['Jobs']['rsync'])) new rsync($this->configuration['Jobs']['rsync']);
        if (isset($this->configuration['Jobs']['FTP'])) new FTP($this->configuration['Jobs']['FTP']);
        if (isset($this->configuration['Jobs']['SFTP'])) new SFTP($this->configuration['Jobs']['SFTP']);

        if (isset($this->configuration['Jobs']['ServerUpdate'])) new ServerUpdate($this->configuration['Jobs']['ServerUpdate']);
        if (isset($this->configuration['Jobs']['ServerCleanup']) && is_bool($this->configuration['Jobs']['ServerCleanup']) && $this->configuration['Jobs']['ServerCleanup'] === true) new ServerCleanup();

        //send mail
        if (isset($this->configuration['Mail']) && is_array($this->configuration['Mail']) && isset($this->configuration['Mail']['Enabled']) && $this->configuration['Mail']['Enabled'] == true) new Mail($this->configuration['Mail'], $this->debug);

        Logging::Logger()->Info('Finished');
    }

}
?>