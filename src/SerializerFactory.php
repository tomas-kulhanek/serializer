<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Tomas Kulhanek
 * Email: info@tirus.cz
 */

namespace TomasKulhanek\Serializer;


use TomasKulhanek\Serializer\Handler\EnumHandler;
use TomasKulhanek\Serializer\Handler\SplFileInfoHandler;
use TomasKulhanek\Serializer\Handler\UuidSerializerHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;

class SerializerFactory
{
    public static function create(){
        $serializer = SerializerBuilder::create();
        $serializer->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(
                new IdenticalPropertyNamingStrategy()
            )
        );
        $serializer->addDefaultHandlers()->configureHandlers(function (HandlerRegistry $registry) {
            $registry->registerSubscribingHandler(new SplFileInfoHandler());
            $registry->registerSubscribingHandler(new UuidSerializerHandler());
            $registry->registerSubscribingHandler(new EnumHandler());
        });
        return $serializer->build();
    }


}
