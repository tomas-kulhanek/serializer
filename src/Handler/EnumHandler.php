<?php

declare(strict_types=1);

namespace TomasKulhanek\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class EnumHandler implements SubscribingHandlerInterface
{
	public static function getSubscribingMethods(): array
	{
		$formats = ['json', 'xml', 'yml'];
		$methods = [];

		foreach ($formats as $format) {
			$methods[] = [
				'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
				'format' => $format,
				'type' => 'enum',
				'method' => 'serializeEnumToJson',
			];
			$methods[] = [
				'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
				'format' => $format,
				'type' => 'enum',
				'method' => 'deserializeEnumFromJson',
			];
		}
		return $methods;
	}

	public function serializeEnumToJson(JsonSerializationVisitor $visitor, \BackedEnum $data, array $type, Context $context): string|int
	{
		return $data->value;
	}

	/**
	 * @template T
	 *
	 * @param array{params: array<array-key, array{name: class-string<T>}>} $type
	 *
	 * @return \BackedEnum|T
	 */
	public function deserializeEnumFromJson(JsonDeserializationVisitor $visitor, mixed $data, array $type, Context $context)
	{
		/** @var ?class-string<T> $type */
		$type = $type['params'][0]['name'] ?? null;
		if (null === $type || !is_a($type, \BackedEnum::class, true)) {
			throw new \LogicException();
		}

		return $type::from($data);
	}
}
