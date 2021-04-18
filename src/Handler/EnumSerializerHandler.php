<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Handler;

use Closure;
use Consistence\Enum\Enum;
use Consistence\Enum\MultiEnum;
use Consistence\Type\ArrayType\ArrayType;
use Consistence\Type\Type;
use TomasKulhanek\Serializer\Enum\EnumValueType;
use TomasKulhanek\Serializer\Exception\DeserializationInvalidValueException;
use TomasKulhanek\Serializer\Exception\MappedClassMismatchException;
use TomasKulhanek\Serializer\Exception\MissingEnumNameException;
use TomasKulhanek\Serializer\Exception\NotEnumException;
use TomasKulhanek\Serializer\Exception\NotIterableValueException;
use TomasKulhanek\Serializer\Exception\NotMultiEnumException;
use TomasKulhanek\Serializer\Exception\SerializationInvalidValueException;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Traversable;

class EnumSerializerHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{

    public const PARAM_MULTI_AS_SINGLE = 'as_single';

    private const PATH_PROPERTY_SEPARATOR = '::';
    private const PATH_FIELD_SEPARATOR = '.';

    public const TYPE_ENUM = 'enum';

    /**
     * @return string[][]
     */
    public static function getSubscribingMethods(): array
    {
        $formats = ['json', 'xml', 'yml'];
        $methods = [];
        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => self::TYPE_ENUM,
                'format' => $format,
                'method' => 'serializeEnum',
            ];
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'type' => self::TYPE_ENUM,
                'format' => $format,
                'method' => 'deserializeEnum',
            ];
        }

        return $methods;
    }

    public function serializeEnum(SerializationVisitorInterface $visitor, Enum $enum, array $type, Context $context)
    {
        try {
            return $this->serializeEnumValue($visitor, $enum, $type);
        } catch (MappedClassMismatchException $e) {
            throw new SerializationInvalidValueException($this->getPropertyPath($context), $e);
        }
    }

    private function serializeEnumValue(SerializationVisitorInterface $visitor, Enum $enum, array $type)
    {
        if ($this->hasEnumClassParameter($type)) {
            $mappedEnumClass = $this->getEnumClass($type);
            $actualEnumClass = get_class($enum);
            if ($mappedEnumClass !== $actualEnumClass) {
                throw new MappedClassMismatchException($mappedEnumClass, $actualEnumClass);
            }
            if ($this->hasAsSingleParameter($type)) {
                $this->checkMultiEnum($actualEnumClass);
                $arrayValueType = [
                    'name' => 'enum',
                    'params' => [
                        [
                            'name' => 'enum',
                            'params' => [
                                [
                                    'name' => $mappedEnumClass::getSingleEnumClass(),
                                    'params' => [],
                                ],
                            ],
                        ],
                    ],
                ];
                return $visitor->visitArray(array_values($enum->getEnums()), $arrayValueType);
            }
        }

        return $this->serializationVisitType($visitor, $enum, $type);
    }

    private function serializationVisitType(SerializationVisitorInterface $visitor, Enum $enum, array $typeMetadata)
    {
        $value = $enum->getValue();
        $valueType = EnumValueType::get(Type::getType($value));

        switch (TRUE) {
            case $valueType->equalsValue(EnumValueType::INTEGER):
                return $visitor->visitInteger($value, $typeMetadata);
            case $valueType->equalsValue(EnumValueType::STRING):
                return $visitor->visitString($value, $typeMetadata);
            case $valueType->equalsValue(EnumValueType::FLOAT):
                return $visitor->visitDouble($value, $typeMetadata);
            case $valueType->equalsValue(EnumValueType::BOOLEAN):
                return $visitor->visitBoolean($value, $typeMetadata);
            // @codeCoverageIgnoreStart
            // should never happen, other types are not allowed in Enums
            default:
                throw new \Exception('Unexpected type');
        }
        // @codeCoverageIgnoreEnd
    }

    public function deserializeEnum(DeserializationVisitorInterface $visitor, $data, array $type, Context $context): Enum
    {
        try {
            return $this->deserializeEnumValue($visitor, $data, $type);
        } catch (\Consistence\Enum\InvalidEnumValueException $e) {
            throw new DeserializationInvalidValueException($this->getFieldPath($context), $e);
        } catch (NotIterableValueException $e) {
            throw new DeserializationInvalidValueException($this->getFieldPath($context), $e);
        }
    }

    private function deserializeEnumValue(DeserializationVisitorInterface $visitor, $data, array $type): Enum
    {
        $enumClass = $this->getEnumClass($type);
        if ($this->hasAsSingleParameter($type)) {
            $this->checkMultiEnum($enumClass);
            $singleEnumClass = $enumClass::getSingleEnumClass();
            if ($singleEnumClass === NULL) {
                throw new \Consistence\Enum\NoSingleEnumSpecifiedException($enumClass);
            }
            $singleEnums = [];
            if (!is_array($data) && !($data instanceof Traversable)) {
                throw new NotIterableValueException($data);
            }
            foreach ($data as $item) {
                $singleEnums[] = $singleEnumClass::get($this->deserializationVisitType($visitor, $item, $type));
            }

            return $enumClass::getMultiByEnums($singleEnums);
        }

        return $enumClass::get($this->deserializationVisitType($visitor, $data, $type));
    }

    private function deserializationVisitType(DeserializationVisitorInterface $visitor, $data, array $typeMetadata)
    {
        $deserializationType = $this->findDeserializationType($typeMetadata);
        if ($deserializationType === NULL) {
            return $data;
        }

        switch (TRUE) {
            case $deserializationType->equalsValue(EnumValueType::INTEGER):
                return $visitor->visitInteger($data, $typeMetadata);
            case $deserializationType->equalsValue(EnumValueType::STRING):
                return $visitor->visitString($data, $typeMetadata);
            case $deserializationType->equalsValue(EnumValueType::FLOAT):
                return $visitor->visitDouble($data, $typeMetadata);
            case $deserializationType->equalsValue(EnumValueType::BOOLEAN):
                return $visitor->visitBoolean($data, $typeMetadata);
            // @codeCoverageIgnoreStart
            // should never happen, other types are not allowed in Enums
            default:
                throw new \Exception('Unexpected type');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param mixed[] $type
     * @return string
     * @throws MissingEnumNameException
     * @throws NotEnumException
     */
    private function getEnumClass(array $type): string
    {
        if (!$this->hasEnumClassParameter($type)) {
            throw new MissingEnumNameException();
        }
        $enumClass = $type['params'][0]['name'];
        if (!is_a($enumClass, Enum::class, TRUE)) {
            throw new NotEnumException($enumClass);
        }

        return $enumClass;
    }

    /**
     * @param mixed[] $type
     * @return bool
     */
    private function hasEnumClassParameter(array $type): bool
    {
        return isset($type['params'][0])
            && isset($type['params'][0]['name']);
    }

    /**
     * @param mixed[] $type
     * @return bool
     */
    private function hasAsSingleParameter(array $type): bool
    {
        return $this->findParameter($type, function (array $parameter): bool {
                return $parameter['name'] === self::PARAM_MULTI_AS_SINGLE;
            }) !== NULL;
    }

    /**
     * @param mixed[] $type
     * @return EnumValueType|null
     */
    private function findDeserializationType(array $type): ?EnumValueType
    {
        $parameter = $this->findParameter($type, function (array $parameter): bool {
            return EnumValueType::isValidValue($parameter['name']);
        });

        if ($parameter === NULL) {
            return NULL;
        }

        return EnumValueType::get($parameter['name']);
    }

    /**
     * @param mixed[] $type
     * @param \Closure $callback
     * @return mixed[]|null
     */
    private function findParameter(array $type, Closure $callback): ?array
    {
        return ArrayType::findValueByCallback($type['params'], $callback);
    }

    private function checkMultiEnum(string $enumClass): void
    {
        if (!is_a($enumClass, MultiEnum::class, TRUE)) {
            throw new NotMultiEnumException($enumClass);
        }
    }

    private function getPropertyPath(Context $context): string
    {
        $path = '';
        $lastPropertyMetadata = NULL;
        foreach ($context->getMetadataStack() as $element) {
            if ($element instanceof PropertyMetadata) {
                $name = $element->name;
                $path = '$' . $name . self::PATH_PROPERTY_SEPARATOR . $path;
                $lastPropertyMetadata = $element;
            }
        }
        if ($lastPropertyMetadata !== NULL) {
            $path = $lastPropertyMetadata->class . self::PATH_PROPERTY_SEPARATOR . $path;
        }
        $path = rtrim($path, self::PATH_PROPERTY_SEPARATOR);

        return $path;
    }

    private function getFieldPath(Context $context): string
    {
        $path = '';
        foreach ($context->getMetadataStack() as $element) {
            if ($element instanceof PropertyMetadata) {
                $name = ($element->serializedName !== NULL) ? $element->serializedName : $element->name;
                $path = $name . self::PATH_FIELD_SEPARATOR . $path;
            }
        }
        $path = rtrim($path, self::PATH_FIELD_SEPARATOR);

        return $path;
    }

}
