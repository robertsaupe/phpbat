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

use splitbrain\PHPArchive\{Tar};

class SelfUpdate {

    public const GITHUB_VERSION = 'https://raw.githubusercontent.com/robertsaupe/phpbat/master/VERSION';
    public const GITHUB_RELEASE = 'https://github.com/robertsaupe/phpbat/releases/download/';

    private false|string $server_version = false;

    public function __construct(
        private bool $enabled,
        private string $local_version
    ) {
        Logging::Logger()->Trace('job\SelfUpdate');

        if (!isset($this->enabled) || !is_bool($this->enabled) || $this->enabled != true) {
            Logging::Logger()->Debug('SelfUpdate: Disabled!');
            return;
        }

        $this->server_version = @file_get_contents(self::GITHUB_VERSION);
        if ($this->server_version) $this->server_version = str_replace("\n", '', $this->server_version);

        if (!isset($this->server_version) || !is_string($this->server_version)) {
            Logging::Logger()->Warn('SelfUpdate: Connection to Server failed!');
        } else {
            $local_version_array = explode('.', $this->local_version);
            $server_version_array = explode('.', $this->server_version);
            if (count($local_version_array) != 3 || count($server_version_array) != 3) {
                Logging::Logger()->Warn('SelfUpdate: Version format missmatch!');
            } else {
                if ($server_version_array[0] > $local_version_array[0]) {
                    Logging::Logger()->Debug('SelfUpdate: Major Update found!');
                    Logging::Logger()->Warn('SelfUpdate: The new version (' . $server_version_array . ') is incompatible with this one, please update manually!');
                } else if ($server_version_array[1] > $local_version_array[1]) {
                    Logging::Logger()->Debug('SelfUpdate: Minor Update found!');
                    $this->do_update();
                } else if ($server_version_array[2] > $local_version_array[2]) {
                    Logging::Logger()->Debug('SelfUpdate: Patch Update found!');
                    $this->do_update();
                } else {
                    Logging::Logger()->Debug('SelfUpdate: You\'re using the latest version!');
                }
            }
        }
    }

    private function do_update() {
        Logging::Logger()->Debug('SelfUpdate: Update from ' . $this->local_version . ' to ' . $this->server_version);
        $tarball = self::GITHUB_RELEASE . $this->server_version . '/' . $this->server_version . '.tar.gz';
        $tarball_sha1 = $tarball . '.sha1';
        $update = @file_get_contents($tarball);
        if (!$update) {
            Logging::Logger()->Error('SelfUpdate: Update-File download failed!');
        } else {
            Logging::Logger()->Debug('SelfUpdate: Update-File downloaded!');
            $sha1 = @file_get_contents($tarball_sha1);
            if (!$sha1) {
                Logging::Logger()->Error('SelfUpdate: Update-SHA1 download failed!');
            } else if (sha1($update) != $sha1) {
                Logging::Logger()->Error('SelfUpdate: Update-File SHA1 missmatch!');
            } else {
                try{
                    @file_put_contents('update.tar.gz', $update);
                    $tar = new Tar();
                    $tar->open('update.tar.gz');
                    $tar->extract('./', 1);
                    $tar->close();
                    Logging::Logger()->Debug('SelfUpdate: Update-File extracted!');
                    @unlink('update.tar.gz');
                    Logging::Logger()->Info('SelfUpdate: Update finished!');
                    $this->restart();
                } catch (\Exception $e) {
                    Logging::Logger()->Error('SelfUpdate: Update-File extract failed!' . $e->getMessage());
                }
            }
        }
    }

    private function restart() {
        if (Dependencies::Exec_Available() && Dependencies::Popen_Available()) {
            Logging::Logger()->Debug('SelfUpdate: Restart App!');
            if (Logging::Logger()->Get_Live_Mode() == 'cron') {
                @exec('php phpBAT.php -cron');
            } else {
                $command = new Command('php phpBAT.php', function($output) {
                    if (Logging::Logger()->Get_Live_Mode() == 'cli') print($output . PHP_EOL);
                    else print(nl2br($output . PHP_EOL));
                });
            }
        } else {
            print('PLEASE RESTART THIS APP!'."\n");
        }
        die();
    }

}

?>