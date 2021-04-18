<?php
/**
 * Created by PhpStorm.
 * User: tomas.kulhanek
 * Date: 16.10.2018
 * Time: 12:09
 */

namespace TomasKulhanek\Serializer\Exception;


class InvalidUuidException extends \Exception
{
    private string $invalidUuid;

    public function __construct(string $invalidUuid, ?\Throwable $exception = NULL)
    {
        parent::__construct(
            sprintf('"%s" is not a valid UUID', $invalidUuid),
            0,
            $exception
        );
        $this->invalidUuid = $invalidUuid;
    }

    public function getInvalidUuid(): string
    {
        return $this->invalidUuid;
    }
}
