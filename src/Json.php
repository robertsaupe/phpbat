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

namespace robertsaupe\phpbat;

use stdClass;
use const JSON_ERROR_NONE;
use const JSON_ERROR_UTF8;
use function json_last_error;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use JsonSchema\Validator;
use Webmozart\Assert\Assert;
use Symfony\Component\Filesystem\Exception\IOException;
use robertsaupe\phpbat\Exception\JsonValidateException;

/**
 * @internal
 */
final class Json {

    private JsonParser $linter;

    public function __construct() {
        $this->linter = new JsonParser();
    }

    public function lint(string $json): void {
        $result = $this->linter->lint($json);

        if ($result instanceof ParsingException) {
            throw $result;
        }
    }

    public function readFile(string $file): string {
        Assert::file($file);
        Assert::readable($file);

        if (false === ($jsonString = @file_get_contents($file))) {
            throw new IOException(
                sprintf(
                    'Failed to read file "%s": %s.',
                    $file,
                    error_get_last()['message'],
                ),
                0,
                null,
                $file,
            );
        }

        return $jsonString;
    }

    public function decodeFile(string $file, bool $assoc = false): array|stdClass {

        $jsonString = $this->readFile($file);

        return $this->decode($jsonString, $assoc);
    }

    public function decode(string $jsonString, bool $assoc = false): array|stdClass {
        $jsonObject = json_decode($jsonString, $assoc);

        if (JSON_ERROR_NONE !== ($error = json_last_error())) {
            // Swallow the UTF-8 error and relies on the lint instead otherwise
            if (JSON_ERROR_UTF8 === $error) {
                throw new ParsingException('JSON decoding failed: Malformed UTF-8 characters, possibly incorrectly encoded');
            }
            $this->lint($jsonString);
        }

        return false === $assoc ? (object) $jsonObject : $jsonObject;   // If JSON is an empty JSON json_decode returns an empty array instead of an stdClass instance
    }

    public function validate(string $file, stdClass $jsonObject, stdClass $schema): void {
        $validator = new Validator();
        $validator->check($jsonObject, $schema);

        if (!$validator->isValid()) {
            $errors = [];

            foreach ($validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'].' : ' : '').$error['message'];
            }

            $message = [] !== $errors
                ? "\"$file\" does not match the expected JSON schema:\n  - ".implode("\n  - ", $errors)
                : "\"$file\" does not match the expected JSON schema."
            ;

            throw new JsonValidateException($message, $file, $errors);
        }
    }

}

?>