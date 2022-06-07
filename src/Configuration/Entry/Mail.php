<?php

declare(strict_types=1);

/*
 * This file is part of the robertsaupe/phpbat package.
 *
 * (c) Robert Saupe <mail@robertsaupe.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace robertsaupe\phpbat\Configuration\Entry;

use stdClass;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Mail {

    public static function create(stdClass $jsonObject): self {

        $key = 'mail';
        $object = (isset($jsonObject->{$key}) ? $jsonObject->{$key} : new stdClass());

        $keyHost = 'host';
        $objectHost = (isset($object->{$keyHost}) ? $object->{$keyHost} : '');
        Assert::string($objectHost, 'Must be a string ' . $key . '.' . $keyHost);
        $objectHost = trim($objectHost);
        Assert::notEmpty($objectHost, 'Cannot be empty ' . $key . '.' . $keyHost);

        $keyUser = 'user';
        $objectUser = (isset($object->{$keyUser}) ? $object->{$keyUser} : '');
        Assert::string($objectUser, 'Must be a string ' . $key . '.' . $keyUser);
        $objectUser = trim($objectUser);
        Assert::notEmpty($objectUser, 'Cannot be empty ' . $key . '.' . $keyUser);

        $keyPassword = 'password';
        $objectPassword = (isset($object->{$keyPassword}) ? $object->{$keyPassword} : '');
        Assert::string($objectPassword, 'Must be a string ' . $key . '.' . $keyPassword);
        $objectPassword = trim($objectPassword);
        Assert::notEmpty($objectPassword, 'Cannot be empty ' . $key . '.' . $keyPassword);

        $keySSL = 'ssl';
        $objectSSL = (isset($object->{$keySSL}) ? $object->{$keySSL} : true);
        Assert::boolean($objectSSL, 'Must be a boolean ' .$key . '.' . $keySSL);

        $keyPort = 'port';
        $objectPort = (isset($object->{$keyPort}) ? $object->{$keyPort} : 465);
        Assert::integer($objectPort, 'Must be a boolean ' .$key . '.' . $keyPort);

        $keyFrom = 'from';
        $objectFrom = (isset($object->{$keyFrom}) ? $object->{$keyFrom} : '');
        Assert::string($objectFrom, 'Must be a string ' . $key . '.' . $keyFrom);
        $objectFrom = trim($objectFrom);
        Assert::notEmpty($objectFrom, 'Cannot be empty ' . $key . '.' . $keyFrom);

        $keyTo = 'to';
        $objectTo = (isset($object->{$keyTo}) ? $object->{$keyTo} : '');
        Assert::string($objectTo, 'Must be a string ' . $key . '.' . $keyTo);
        $objectTo = trim($objectTo);
        Assert::notEmpty($objectTo, 'Cannot be empty ' . $key . '.' . $keyTo);

        return new self(
            $objectHost,
            $objectUser,
            $objectPassword,
            $objectSSL,
            $objectPort,
            $objectFrom,
            $objectTo
        );
    }

    private function __construct(
        private string $host,
        private string $user,
        private string $password,
        private bool $ssl,
        private int $port,
        private string $from,
        private string $to,
    ) {
        
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getSSL(): bool {
        return $this->ssl;
    }

    public function getPort(): int {
        return $this->port;
    }

    public function getFrom(): string {
        return $this->from;
    }

    public function getTo(): string {
        return $this->to;
    }

}

?>