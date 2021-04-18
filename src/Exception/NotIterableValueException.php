<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Exception;

use Consistence\Type\Type;

class NotIterableValueException extends \Consistence\PhpException
{

    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     * @param \Throwable|null $previous
     */
    public function __construct($value, ?\Throwable $previous = NULL)
    {
        parent::__construct(sprintf('Value of type %s is not iterable', Type::getType($value)), $previous);
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
