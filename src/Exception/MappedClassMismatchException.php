<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Exception;

class MappedClassMismatchException extends \Consistence\PhpException
{
    private string $mappedClassName;

    private string $valueClassName;

    public function __construct(string $mappedClassName, string $valueClassName, ?\Throwable $previous = NULL)
    {
        parent::__construct(sprintf(
            'Class of given value "%s" does not match mapped %s<%s>',
            $valueClassName,
            EnumSerializerHandler::TYPE_ENUM,
            $mappedClassName
        ), $previous);
        $this->mappedClassName = $mappedClassName;
        $this->valueClassName = $valueClassName;
    }

    public function getMappedClassName(): string
    {
        return $this->mappedClassName;
    }

    public function getValueClassName(): string
    {
        return $this->valueClassName;
    }

}
