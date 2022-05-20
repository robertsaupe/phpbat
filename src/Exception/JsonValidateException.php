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

namespace robertsaupe\phpbat\Exception;

use Throwable;
use UnexpectedValueException;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class JsonValidateException extends UnexpectedValueException {

    private $validatedFile;
    private $errors;

    public function __construct(
        string $message,
        ?string $file = null,
        array $errors = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        if (null !== $file) {
            Assert::file($file);
        }
        Assert::allString($errors);
        
        $this->validatedFile = $file;
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    public function getValidatedFile(): ?string {
        return $this->validatedFile;
    }

    public function getErrors(): array {
        return $this->errors;
    }

}

?>