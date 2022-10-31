<?php

/*
 * This file is part of the robertsaupe/phpbat package.
 *
 * (c) Robert Saupe <mail@robertsaupe.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use robertsaupe\phpbat\Console\Application;
use robertsaupe\Json\Json;

define('APP_DIR', dirname(__DIR__));

include_once(APP_DIR . '/vendor/autoload.php');

define('COMPOSER_MANIFEST', APP_DIR . '/composer.json');

define('FILE_NAME', 'phpbat.phar');
define('BUILD_FILE', APP_DIR . '/build/' . FILE_NAME);

define('RELEASE_DIR', dirname(APP_DIR) . '/gh-pages/release');
define('RELEASE_MANIFEST', RELEASE_DIR . '/manifest.json');

define('RELEASE_REMOTE_URL', 'https://robertsaupe.github.io/phpbat/release');

function release(string $version = Application::VERSION): void {

    print('add/update release: ' . $version . PHP_EOL . PHP_EOL);

    $release_path = RELEASE_DIR . '/' . $version;
    $release_file = $release_path . '/' . FILE_NAME;

    print('copy build phar: ');

    if (!is_dir($release_path)) {
        if (!@mkdir($release_path, recursive:true)) {
            print('failed' . PHP_EOL);
            $errors = error_get_last();
            print('MKDIR ERROR: ' . $errors['type'] . PHP_EOL);
            print($errors['message'] . PHP_EOL);
            exit();
        }
    }

    if (@copy(BUILD_FILE, $release_file)) {
        print('success' . PHP_EOL);
    } else {
        print('failed' . PHP_EOL);
        $errors = error_get_last();
        print('COPY ERROR: ' . $errors['type'] . PHP_EOL);
        print($errors['message'] . PHP_EOL);
        exit();
    }

    if (!file_exists($release_file)) {
        print('FILE: ' . $release_file . ' does not exist!' . PHP_EOL);
        exit();
    }
    
    $release_hash_sha1 = hash_file('sha1', $release_file);
    $release_hash_sha256 = hash_file('sha256', $release_file);
    $release_hash_sha512 = hash_file('sha512', $release_file);

    print(PHP_EOL);

    $release_hash_sha1_file = $release_path . '/sha1';
    print('SHA1: ' . $release_hash_sha1 . PHP_EOL);
    print('SHA1-FILE: ' . $release_hash_sha1_file);
    if (@file_put_contents($release_hash_sha1_file, $release_hash_sha1)) {
        print(' created.' . PHP_EOL);
    } else {
        print(' create failed!' . PHP_EOL);
        $errors = error_get_last();
        print('WRITE ERROR: ' . $errors['type'] . PHP_EOL);
        print($errors['message'] . PHP_EOL);
        exit();
    }

    print(PHP_EOL);

    $release_hash_sha256_file = $release_path . '/sha256';
    print('SHA256: ' . $release_hash_sha256 . PHP_EOL);
    print('SHA256-FILE: ' . $release_hash_sha256_file);
    if (@file_put_contents($release_hash_sha256_file, $release_hash_sha256)) {
        print(' created.' . PHP_EOL);
    } else {
        print(' create failed!' . PHP_EOL);
        $errors = error_get_last();
        print('WRITE ERROR: ' . $errors['type'] . PHP_EOL);
        print($errors['message'] . PHP_EOL);
        exit();
    }

    print(PHP_EOL);

    $release_hash_sha512_file = $release_path . '/sha512';
    print('SHA512: ' . $release_hash_sha512 . PHP_EOL);
    print('SHA512-FILE: ' . $release_hash_sha512_file);
    if (@file_put_contents($release_hash_sha512_file, $release_hash_sha512)) {
        print(' created.' . PHP_EOL);
    } else {
        print(' create failed!' . PHP_EOL);
        $errors = error_get_last();
        print('WRITE ERROR: ' . $errors['type'] . PHP_EOL);
        print($errors['message'] . PHP_EOL);
        exit();
    }

    print(PHP_EOL);

    $json = new Json();
    $jsonObject = [];
    if (file_exists(RELEASE_MANIFEST)) {
        $jsonObject = $json->decodeFile(RELEASE_MANIFEST, true);
    }

    $jsonObjectComposer = $json->decodeFile(COMPOSER_MANIFEST, true);
    
    /** @phpstan-ignore-next-line */
    $jsonObject[$version] = [
        "version" => Application::VERSION,
        "url" => RELEASE_REMOTE_URL . '/' . $version . '/' . FILE_NAME,
        "sha1" => $release_hash_sha1,
        "sha256" => $release_hash_sha256,
        "sha512" => $release_hash_sha512,
        "php" => [
            /** @phpstan-ignore-next-line */
            "min" => $jsonObjectComposer["config"]["platform"]["php"]
        ]
    ];
    
    $jsonString = json_encode($jsonObject, JSON_PRETTY_PRINT);
    
    @file_put_contents(RELEASE_MANIFEST, $jsonString);

    print('MANIFEST-FILE: ' . RELEASE_MANIFEST);
    if (@file_put_contents(RELEASE_MANIFEST, $jsonString)) {
        print(' created.' . PHP_EOL);
    } else {
        print(' create failed!' . PHP_EOL);
        $errors = error_get_last();
        print('WRITE ERROR: ' . $errors['type'] . PHP_EOL);
        print($errors['message'] . PHP_EOL);
        exit();
    }

    print(PHP_EOL.PHP_EOL);

}

print(PHP_EOL.PHP_EOL);

release();

/** @phpstan-ignore-next-line */
if (Application::VERSION_RELEASE === 'stable' || Application::VERSION_RELEASE === '') {
    print(PHP_EOL.PHP_EOL);
    release('latest');
    release('latest-stable');
    release('stable');
} else {
    print(PHP_EOL.PHP_EOL);
    release('latest-unstable');
    release('unstable');
    if (!is_dir(RELEASE_DIR . '/stable')) {
        release('latest');
    }
}

print('ALL DONE' . PHP_EOL . PHP_EOL);

?>