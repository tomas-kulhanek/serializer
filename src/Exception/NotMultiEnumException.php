<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Exception;

class NotMultiEnumException extends \Consistence\PhpException
{
    private string $className;

    public function __construct(string $className, ?\Throwable $previous = NULL)
    {
        parent::__construct(sprintf('Class "%s" is not an MultiEnum', $className), $previous);
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

}
