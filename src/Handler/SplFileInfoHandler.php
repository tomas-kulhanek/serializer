<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Tomas Kulhanek
 * Email: info@tirus.cz
 */

namespace TomasKulhanek\Serializer\Handler;

use TomasKulhanek\Serializer\Utils\SplFileInfo;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class SplFileInfoHandler implements SubscribingHandlerInterface
{
    private const TYPE = 'base64File';


    public static function getSubscribingMethods(): array
    {
        $formats = [
            'json',
            'xml',
            'yml',
        ];
        $methods = [];
        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => self::TYPE,
                'format' => $format,
                'method' => 'serializeSplFileInfo',
            ];
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => self::TYPE,
                'format' => $format,
                'method' => 'deserializeSplFileInfo',
            ];
        }
        return $methods;
    }


    public function serializeSplFileInfo(VisitorInterface $visitor, SplFileInfo $content, array $type, Context $context)
    {

        return $visitor->visitString(base64_encode($content->getContents()), $type, $context);
    }

    public function deserializeSplFileInfo(VisitorInterface $visitor, $content, array $type, Context $context)
    {
        if ((string)$content == null) {
            return null;
        }
        return SplFileInfo::createInTemp(base64_decode((string)$content));
    }
}
