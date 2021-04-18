<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: tomas.kulhanek
 * Date: 16.10.2018
 * Time: 12:06
 */

namespace HelpPC\Serializer\Tests;

use HelpPC\Serializer\Exception\DeserializationInvalidValueException;
use HelpPC\Serializer\Exception\InvalidUuidException;
use HelpPC\Serializer\Handler\UuidSerializerHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Ramsey\Uuid\Uuid;
use Tester\{
    Assert, Environment, TestCase
};

require __DIR__ . '/data/Car.php';
require __DIR__ . '/../vendor/autoload.php';
Environment::setup();
date_default_timezone_set('Europe/Prague');
\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

class UuidSerializerHandlerTest extends TestCase
{
    public function testSerializeUuidToJson(): void
    {
        $car = new Car();
        $car->id = Uuid::fromString('86be949f-7f46-4457-9230-fad9783337aa');
        $serializer = $this->getSerializer();
        $json = $serializer->serialize($car, 'json');
        Assert::equal('{"id":"86be949f-7f46-4457-9230-fad9783337aa"}', $json);
    }

    public function testSerializeUuidToXml(): void
    {
        $car = new Car();
        $car->id = Uuid::fromString('86be949f-7f46-4457-9230-fad9783337aa');
        $serializer = $this->getSerializer();
        $json = $serializer->serialize($car, 'xml');
        Assert::equal(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<result>' . "\n" .
            '  <id><![CDATA[86be949f-7f46-4457-9230-fad9783337aa]]></id>' . "\n" .
            '</result>' . "\n",
            $json
        );
    }

    public function testDeserializeUuidFromJson(): void
    {
        $expectedUuid = Uuid::fromString('86be949f-7f46-4457-9230-fad9783337aa');
        $serializer = $this->getSerializer();
        /** @var Car $car */
        $car = $serializer->deserialize('{
			"id":"86be949f-7f46-4457-9230-fad9783337aa"
		}', Car::class, 'json');

        Assert::type(Car::class, $car);
        Assert::true($car->id->equals($expectedUuid));
    }

    public function testDeserializeUuidFromXml(): void
    {
        $expectedUuid = Uuid::fromString('86be949f-7f46-4457-9230-fad9783337aa');
        $serializer = $this->getSerializer();
        /** @var Car $car */
        $car = $serializer->deserialize(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<result>' . "\n" .
            '  <id><![CDATA[86be949f-7f46-4457-9230-fad9783337aa]]></id>' . "\n" .
            '</result>' . "\n",
            Car::class,
            'xml'
        );
        Assert::type(Car::class, $car);
        Assert::true($car->id->equals($expectedUuid));
    }

    public function testDeserializeInvalidUuid(): void
    {
        $serializer = $this->getSerializer();
        try {
            $serializer->deserialize('{
				"id":"86be949f-7f46-4457-9230-fad9783337xx"
			}', Car::class, 'json');
            $this->fail();
        } catch (DeserializationInvalidValueException $e) {
            Assert::equal('id', $e->getFieldPath());
            /** @var InvalidUuidException $previous */
            $previous = $e->getPrevious();
            Assert::equal('86be949f-7f46-4457-9230-fad9783337xx', $previous->getInvalidUuid());
        }
    }

    private function getSerializer(): Serializer
    {
        return SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new UuidSerializerHandler());
            })
            ->build();
    }
}


(new UuidSerializerHandlerTest())->run();