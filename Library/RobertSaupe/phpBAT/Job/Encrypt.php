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

use RobertSaupe\phpBAT\Helper\{Encryption, FileMgr, Logging};

class Encrypt {

    public function __construct(
        private string $file,
        private ?string $chmod = null,
        private bool $encrypt = false,
        private string $encrypt_cipher = '',
        private string $encrypt_password = ''
    ) {
        Logging::Logger()->Trace('job\Encrypt');
        if (!isset($this->file) || !is_string($this->file) || !file_exists($this->file)) {
            Logging::Logger()->Error('Encrypt: file not exists');
        } else if ($this->encrypt !== true) {
            Logging::Logger()->Debug('Encrypt: skipped (disabled)');
        } else if (strlen($this->encrypt_cipher) < 1) {
            Logging::Logger()->Error('Encrypt: Cipher not set');
        } else if (strlen($this->encrypt_password) < 1) {
            Logging::Logger()->Error('Encrypt: Password not set');
        } else if (!function_exists('openssl_encrypt') || !function_exists('openssl_get_cipher_methods')) {
            Logging::Logger()->Error('Encrypt: OpenSSL not available!');
        } else if (!in_array($this->encrypt_cipher, openssl_get_cipher_methods())) {
            Logging::Logger()->Error('Encrypt: Cipher: ' . $this->encrypt_cipher . ' not available!');
            Logging::Logger()->Warn('Encrypt: Available Cipher methods: ' . implode(', ', openssl_get_cipher_methods()));
        } else if (!Encryption::Encrypt_File($this->file, $this->encrypt_cipher, $this->encrypt_password, $this->file . '.enc')) {
            Logging::Logger()->Error('Encrypt: File: ' . $this->file . ' failed!');
        } else {
            Logging::Logger()->Info('Encrypt: encrypted File: ' . $this->file . '.enc' . ' created!');
            FileMgr::CHMOD($this->file . '.enc', $this->chmod);
            if (!@unlink($this->file)) Logging::Logger()->Warn('Encrypt: unencrypted file: ' . $this->file . ' not deleted!');
            else Logging::Logger()->Debug('Encrypt: unencrypted file: ' . $this->file . ' deleted!');
        }
    }

}

?>