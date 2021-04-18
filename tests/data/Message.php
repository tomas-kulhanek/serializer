<?php
/**
 * Created by PhpStorm.
 * User: tomas.kulhanek
 * Date: 16.10.2018
 * Time: 12:19
 */

namespace HelpPC\Serializer\Tests;

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