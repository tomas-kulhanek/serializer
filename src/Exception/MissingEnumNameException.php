<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Exception;

class MissingEnumNameException extends \Consistence\PhpException
{

    public function __construct(?\Throwable $previous = NULL)
    {
        parent::__construct('Missing enum class name', $previous);
    }

}
