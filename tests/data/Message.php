<?php

namespace TomasKulhanek\Serializer\Tests;

use HelpPC\Serializer\Utils\SplFileInfo;
use JMS\Serializer\Annotation as JMS;

class Message
{
    /**
     * @JMS\Type("base64File")
     * @var SplFileInfo
     */
    public $content;
}