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

class Encryption {

    private const FILE_ENCRYPTION_BLOCKS = 10000;

    /**
    * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
    *
    * @param string $source Path to file that should be encrypted
    * @param string $key    The key used for the encryption
    * @param string $dest   File name where the encryped file should be written to.
    * @return string|false  Returns the file name that has been created or FALSE if an error occured
    * @link https://www.php.net/manual/de/function.openssl-encrypt.php
    */
    public static function Encrypt_File($source, $cipher, $key, $dest) {
        $key = substr(sha1($key, true), 0, 16);
        $iv = openssl_random_pseudo_bytes(16);
        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // Put the initialzation vector to the beginning of the file
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * self::FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

    /**
    * Dencrypt the passed file and saves the result in a new file, removing the last 4 characters from file name.
    *
    * @param string $source Path to file that should be decrypted
    * @param string $key    The key used for the decryption (must be the same as for encryption)
    * @param string $dest   File name where the decryped file should be written to.
    * @return string|false  Returns the file name that has been created or FALSE if an error occured
    * @link https://www.php.net/manual/de/function.openssl-encrypt.php
    */
    public static function Decrypt_File($source, $cipher, $key, $dest) {
        $key = substr(sha1($key, true), 0, 16);
        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            if ($fpIn = fopen($source, 'rb')) {
                // Get the initialzation vector from the beginning of the file
                $iv = fread($fpIn, 16);
                while (!feof($fpIn)) {
                    // we have to read one block more for decrypting than for encrypting
                    $ciphertext = fread($fpIn, 16 * (self::FILE_ENCRYPTION_BLOCKS + 1));
                    $plaintext = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $plaintext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

}