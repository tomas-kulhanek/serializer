<?php
/**
 * Created by PhpStorm.
 * User: tomas.kulhanek
 * Date: 16.10.2018
 * Time: 12:10
 */

namespace TomasKulhanek\Serializer\Exception;


class DeserializationInvalidValueException extends \Exception
{
    private string $fieldPath;

    public function __construct(string $fieldPath, \Throwable $exception)
    {
        parent::__construct(
            sprintf('Invalid value in field %s: %s', $fieldPath, $exception->getMessage()),
            0,
            $exception
        );
        $this->fieldPath = $fieldPath;
    }

    public function getFieldPath(): string
    {
        return $this->fieldPath;
    }
}
