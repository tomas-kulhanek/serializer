<?php
/**
 * Created by PhpStorm.
 * User: tomas.kulhanek
 * Date: 16.10.2018
 * Time: 12:05
 */

declare(strict_types=1);

namespace HelpPC\Serializer\Tests;

use JMS\Serializer\Annotation as JMS;

class Car
{
    /**
     * @JMS\Type("uuid")
     * @var \Ramsey\Uuid\UuidInterface
     */
    public $id;
}