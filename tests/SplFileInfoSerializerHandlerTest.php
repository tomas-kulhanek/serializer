<?php declare(strict_types=1);

namespace TomasKulhanek\Serializer\Tests;

use TomasKulhanek\Serializer\SerializerFactory;
use TomasKulhanek\Serializer\Utils\SplFileInfo;
use JMS\Serializer\Serializer;

use Tester\{
    Assert, Environment, TestCase
};

require __DIR__ . '/data/Message.php';
require __DIR__ . '/../vendor/autoload.php';
Environment::setup();
date_default_timezone_set('Europe/Prague');
\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

class SplFileInfoSerializerHandlerTest extends TestCase
{
    private const TESTFILE = __DIR__ . '/data/testFile.txt';
    private const B64TESTFILE = __DIR__ . '/data/base64OfTestFile.txt';

    public function testSerializeFileToJson(): void
    {
        $message = new Message();
        $message->content = SplFileInfo::createInTemp(file_get_contents(self::TESTFILE));

        $serializer = $this->getSerializer();
        $json = $serializer->serialize($message, 'json');
        Assert::equal('{"content":"' . file_get_contents(self::B64TESTFILE) . '"}', $json);
    }

    public function testSerializeFileToXml(): void
    {
        $message = new Message();
        $message->content = SplFileInfo::createInTemp(file_get_contents(self::TESTFILE));
        $serializer = $this->getSerializer();
        $json = $serializer->serialize($message, 'xml');
        Assert::equal(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<result>' . "\n" .
            '  <content><![CDATA[' . file_get_contents(self::B64TESTFILE) . ']]></content>' . "\n" .
            '</result>' . "\n",
            $json
        );
    }

    public function testDeserializeFileFromJson(): void
    {
        $serializer = $this->getSerializer();
        $message = $serializer->deserialize('{
			"content":"' . file_get_contents(self::B64TESTFILE) . '"
		}', Message::class, 'json');

        Assert::type(Message::class, $message);
    }

    public function testDeserializeFileFromXml(): void
    {
        $serializer = $this->getSerializer();
        $message = $serializer->deserialize(
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<result>' . "\n" .
            '  <content><![CDATA[' . file_get_contents(self::B64TESTFILE) . ']]></content>' . "\n" .
            '</result>' . "\n",
            Message::class,
            'xml'
        );
        Assert::type(Message::class, $message);
    }

    private function getSerializer(): Serializer
    {
        return SerializerFactory::create();
    }
}


(new SplFileInfoSerializerHandlerTest())->run();